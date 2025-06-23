<?php

namespace App\Filament\Resources;

use App\Models\WorkOrder;
use App\Filament\Resources\WorkOrderResource\RelationManagers\WorkOrderItemsRelationManager;
use App\Filament\Resources\ArchivedWorkOrderResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;

class ArchivedWorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Arhiva radnih naloga';
    protected static ?string $navigationGroup = 'Proizvodnja';
    protected static ?int $navigationSort = 2;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArchivedWorkOrders::route('/'),
            // 'view' => Pages\ViewArchivedWorkOrder::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            WorkOrderItemsRelationManager::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return WorkOrderResource::table($table)
            ->actions([]) // ❌ nema pojedinačnih akcija
            ->bulkActions([]) // ❌ nema grupnih akcija
            ->recordAction(null) // ❌ onemogući klik na red
            ->recordUrl(null); // ❌ onemogući link
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('status', 'zavrsen')
                    ->where('is_transferred_to_warehouse', true);
                })->orWhere(function ($q) {
                    $q->where('status', 'zavrsen')
                    ->where('type', 'custom');
                });
            });
    }
}
