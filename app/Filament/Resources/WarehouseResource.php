<?php

namespace App\Filament\Resources;

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
                Tables\Columns\TextColumn::make('product.code')->label('Šifra proizvoda'),
                Tables\Columns\TextColumn::make('product.name')->label('Naziv proizvoda'),
                Tables\Columns\TextColumn::make('quantity')->label('Količina'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Datum unosa')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Proizvod')
                    ->options(
                        \App\Models\Product::query()
                            ->pluck('name', 'id')
                    ),

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
