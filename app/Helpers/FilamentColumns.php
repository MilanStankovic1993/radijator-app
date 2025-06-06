<?php

namespace App\Helpers;

use Filament\Tables\Columns\TextColumn;

class FilamentColumns
{
    public static function userTrackingColumns(): array
    {
        return [
            TextColumn::make('creator.name')
                ->label('Kreirao')
                ->sortable()
                ->toggleable(),

            TextColumn::make('updater.name')
                ->label('Izmenio')
                ->sortable()
                ->toggleable(),

            TextColumn::make('created_at')
                ->label('Kreirano')
                ->dateTime('d.m.Y H:i')
                ->sortable()
                ->toggleable(),

            TextColumn::make('updated_at')
                ->label('Izmenjeno')
                ->dateTime('d.m.Y H:i')
                ->sortable()
                ->toggleable(),
        ];
    }
}
