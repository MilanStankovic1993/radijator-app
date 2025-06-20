<?php

namespace App\Filament\Resources;

use App\Helpers\FilamentColumns;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Traits\HasResourcePermissions;

class WarehouseResource extends Resource
{
    // use HasResourcePermissions;

    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box'; // ikonica
    protected static ?string $navigationLabel = 'Magacin';
    protected static ?string $modelLabel = 'Magacin';
    // protected static ?int $navigationSort = 3;
    public static function getNavigationGroup(): ?string
    {
        return 'Magacin';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Proizvod')
                    ->options(
                        \App\Models\Product::query()
                            ->get()
                            ->mapWithKeys(fn ($product) => [
                                $product->id => "{$product->code} - {$product->name}"
                            ])
                    )
                    ->searchable()
                    ->required()
                    ->preload(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Količina')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.code')->label('Šifra proizvoda')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('product.name')->label('Naziv proizvoda')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('quantity')->label('Količina')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'na_cekanju' => 'warning',
                        'na_stanju' => 'success',
                        'izdato' => 'danger',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'na_cekanju' => 'Na čekanju',
                        'na_stanju' => 'Na stanju',
                        'izdato' => 'Izdato',
                        default => ucfirst($state),
                    })
                    ->sortable()
                    ->toggleable(),
                ...FilamentColumns::userTrackingColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Proizvod')
                    ->options(
                        \App\Models\Product::query()
                            ->pluck('name', 'id')
                    ),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'na_cekanju' => 'Na čekanju',
                        'na_stanju' => 'Na stanju',
                        'izdato' => 'Izdato',
                    ]),
                Tables\Filters\Filter::make('created_today')
                    ->label('Uneto danas')
                    ->query(fn (Builder $query) => $query->whereDate('created_at', now()->toDateString())),

                Tables\Filters\Filter::make('this_week')
                    ->label('Uneto ove nedelje')
                    ->query(fn (Builder $query) =>
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('accept_stock')
                    ->label('Prihvati na zalihe')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Warehouse $record) => $record->isPending())
                    ->form([
                        Forms\Components\TextInput::make('accepted_quantity')
                            ->label('Količina za prijem')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn (Warehouse $record) => $record->quantity)
                            ->required(),
                    ])
                    ->action(function (array $data, Warehouse $record) {
                        $quantityToAccept = (int) $data['accepted_quantity'];

                        // Smanji količinu iz zapisa koji je na čekanju
                        $record->decrement('quantity', $quantityToAccept);

                        // Ako je količina pala na 0, obriši red
                        if ($record->quantity <= 0) {
                            $record->delete();
                        }

                        // Povećaj količinu u 'na_stanju' ako postoji
                        $existing = Warehouse::where('product_id', $record->product_id)
                            ->where('location', $record->location)
                            ->where('status', Warehouse::STATUS_NA_STANJU)
                            ->first();

                        if ($existing) {
                            $existing->increment('quantity', $quantityToAccept);
                        } else {
                            Warehouse::create([
                                'product_id' => $record->product_id,
                                'location' => $record->location,
                                'status' => Warehouse::STATUS_NA_STANJU,
                                'quantity' => $quantityToAccept,
                                'created_by' => auth()->id(),
                                'updated_by' => auth()->id(),
                            ]);
                        }
                    }),

                Tables\Actions\Action::make('issue_stock')
                    ->label('Izdaj robu')
                    ->icon('heroicon-o-truck')
                    ->color('danger')
                    ->visible(fn (Warehouse $record) => $record->isAvailable())
                    ->form([
                        Forms\Components\TextInput::make('issue_quantity')
                            ->label('Količina za izdavanje')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn (Warehouse $record) => $record->quantity)
                            ->required(),
                    ])
                    ->action(function (array $data, Warehouse $record) {
                        $quantityToIssue = (int) $data['issue_quantity'];

                        // Pronađi 'izdato' red (mora pre update-a)
                        $issued = Warehouse::where('product_id', $record->product_id)
                            ->where('location', $record->location)
                            ->where('status', Warehouse::STATUS_IZDATO)
                            ->first();

                        // Ako postoji 'izdato', dodaj količinu
                        if ($issued) {
                            $issued->increment('quantity', $quantityToIssue);
                        } else {
                            Warehouse::create([
                                'product_id' => $record->product_id,
                                'location' => $record->location,
                                'status' => Warehouse::STATUS_IZDATO,
                                'quantity' => $quantityToIssue,
                                'created_by' => auth()->id(),
                                'updated_by' => auth()->id(),
                            ]);
                        }

                        // Smanji količinu
                        $record->decrement('quantity', $quantityToIssue);

                        // Ako nema više na stanju, briši red
                        if ($record->quantity <= 0) {
                            $record->delete();
                        }
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}
