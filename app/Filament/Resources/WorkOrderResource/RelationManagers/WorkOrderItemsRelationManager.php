<?php

namespace App\Filament\Resources\WorkOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;

class WorkOrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Stavke';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID'),
                Tables\Columns\TextColumn::make('code')->label('Kod')->placeholder('Nije unet'),
                Tables\Columns\TextColumn::make('created_at')->label('Kreirano')->dateTime(),
            ]);
    }
}
