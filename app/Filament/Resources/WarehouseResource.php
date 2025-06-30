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
    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Magacin';
    protected static ?string $modelLabel = 'Magacin';

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
                        Warehouse::STATUS_NA_CEKANJU => 'warning',
                        Warehouse::STATUS_NA_STANJU => 'success',
                        Warehouse::STATUS_IZDATO => 'danger',
                    ])
                    ->formatStateUsing(fn ($state) => Warehouse::getStatusOptions()[$state] ?? ucfirst($state))
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
                    ->options(Warehouse::getStatusOptions()),
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
                    ->form(fn (Warehouse $record) => [
                        Forms\Components\CheckboxList::make('selected_codes')
                            ->label('Odaberi kodove za prijem')
                            ->options(
                                $record->items()
                                    ->where('status', Warehouse::STATUS_NA_CEKANJU)
                                    ->get()
                                    ->filter(fn ($item) => is_string($item->code))
                                    ->mapWithKeys(fn ($item) => [
                                        (string) $item->code => 'Kod: ' . $item->code . ' | RN: ' . $item->work_order_id
                                    ])
                                    ->toArray()
                            )
                            // ->bulkToggleable()
                            ->columns(2)
                            ->required(),
                    ])
                    ->action(function (array $data, Warehouse $record) {
                        $selectedCodes = $data['selected_codes'] ?? [];
                        if (empty($selectedCodes)) return;

                        $items = \App\Models\WarehouseItem::query()
                            ->whereIn('code', $selectedCodes)
                            ->where('status', Warehouse::STATUS_NA_CEKANJU)
                            ->get();

                        if ($items->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Greška')
                                ->body('Nijedan kod nije validan za ovaj magacin.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Pronađi red koji je već "na stanju", ili napravi novi
                        $existing = Warehouse::firstOrCreate(
                            [
                                'product_id' => $record->product_id,
                                'location' => $record->location,
                                'status' => Warehouse::STATUS_NA_STANJU,
                            ],
                            [
                                'quantity' => 0,
                                'created_by' => auth()->id(),
                                'updated_by' => auth()->id(),
                            ]
                        );

                        foreach ($items as $item) {
                            $item->update([
                                'status' => Warehouse::STATUS_NA_STANJU,
                                'warehouse_id' => $existing->id,
                                'updated_by' => auth()->id(),
                            ]);
                        }

                        $record->decrement('quantity', count($selectedCodes));
                        $existing->increment('quantity', count($selectedCodes));

                        if ($record->fresh()->quantity <= 0) {
                            $record->delete(); // brišemo samo ako je potpuno prazan
                        }
                    }),

                    Tables\Actions\Action::make('issue_stock')
                        ->label('Izdaj robu')
                        ->icon('heroicon-o-truck')
                        ->color('danger')
                        ->visible(fn (Warehouse $record) => $record->isAvailable())
                        ->form(function (Warehouse $record) {
                            $items = \App\Models\WarehouseItem::query()
                                ->where('product_id', $record->product_id)
                                ->where('location', $record->location)
                                ->where('status', Warehouse::STATUS_NA_STANJU)
                                ->get()
                                ->filter(fn ($item) => is_string($item->code));

                            return [
                                Forms\Components\CheckboxList::make('selected_codes')
                                    ->label('Odaberi kodove za izdavanje')
                                    ->options(
                                        $items->mapWithKeys(fn ($item) => [
                                            (string) $item->code => 'Kod: ' . $item->code . ' | RN: ' . $item->work_order_id
                                        ])->toArray()
                                    )
                                    // ->bulkToggleable()
                                    ->columns(2)
                                    ->required(),
                            ];
                        })
                    ->action(function (array $data, Warehouse $record) {
                        $selectedCodes = $data['selected_codes'] ?? [];

                        if (empty($selectedCodes)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Greška')
                                ->body('Nijedan kod nije izabran za izdavanje.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $itemsToIssue = \App\Models\WarehouseItem::query()
                            ->whereIn('code', $selectedCodes)
                            ->where('status', Warehouse::STATUS_NA_STANJU)
                            ->get();

                        // Pronađi ili kreiraj red za "izdato"
                        $existing = Warehouse::firstOrCreate(
                            [
                                'product_id' => $record->product_id,
                                'location' => $record->location,
                                'status' => Warehouse::STATUS_IZDATO,
                            ],
                            [
                                'quantity' => 0,
                                'created_by' => auth()->id(),
                                'updated_by' => auth()->id(),
                            ]
                        );

                        foreach ($itemsToIssue as $item) {
                            $item->update([
                                'status' => Warehouse::STATUS_IZDATO,
                                'warehouse_id' => $existing->id,
                                'updated_by' => auth()->id(),
                            ]);
                        }

                        $record->decrement('quantity', count($itemsToIssue));
                        $existing->increment('quantity', count($itemsToIssue));

                        if ($record->fresh()->quantity <= 0) {
                            $record->delete(); // brišemo samo ako je prazan
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Uspešno')
                            ->body("Izdato " . count($itemsToIssue) . " komada.")
                            ->success()
                            ->send();
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
        return [];
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
