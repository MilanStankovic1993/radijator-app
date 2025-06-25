<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Korisnici i status tiketa';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Ime korisnika'),

                Tables\Columns\IconColumn::make('pivot.is_read')
                    ->label('Pregledao')
                    ->boolean(),

                Tables\Columns\IconColumn::make('pivot.is_done')
                    ->label('Odradio')
                    ->boolean(),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }
}
