<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseItemResource\Pages;
use App\Models\WarehouseItem;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WarehouseItemResource extends Resource
{
    protected static ?string $model = WarehouseItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Artikli po kodu';
    protected static ?string $modelLabel = 'Artikal';
    protected static ?string $pluralModelLabel = 'Artikli';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Magacin';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kod')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('product.name')->label('Proizvod')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('location')->label('Lokacija')->sortable(),
                Tables\Columns\BadgeColumn::make('status')->label('Status')
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
                    }),
                Tables\Columns\TextColumn::make('quantity')->label('Količina'),
                Tables\Columns\TextColumn::make('workOrder.full_name')->label('Radni nalog'),
                Tables\Columns\TextColumn::make('created_at')->label('Datum')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'na_cekanju' => 'Na čekanju',
                        'na_stanju' => 'Na stanju',
                        'izdato' => 'Izdato',
                    ]),
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Proizvod')
                    ->relationship('product', 'name'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouseItems::route('/'),
            'create' => Pages\CreateWarehouseItem::route('/create'),
            'edit' => Pages\EditWarehouseItem::route('/{record}/edit'),
        ];
    }
}
