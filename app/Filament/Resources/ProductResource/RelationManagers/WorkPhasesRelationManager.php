<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Form;
use Filament\Tables\Table;

class WorkPhasesRelationManager extends RelationManager
{
    protected static string $relationship = 'workPhases'; // naziv relacije u Product modelu

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Naziv')->required(),
            Forms\Components\Select::make('location')->label('Lokacija')->options([
                'Grdica' => 'Grdica',
                'Seovac' => 'Seovac',
            ])->required(),
            Forms\Components\TextInput::make('time_norm')->label('Norma (min)')->numeric()->suffix('min')->required(),
            Forms\Components\TextInput::make('number_of_workers')->label('Broj radnika')->numeric()->required(),
            Forms\Components\Textarea::make('description')->label('Opis'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naziv')
                    ->sortable()
                    ->searchable(query: function ($query, $search) {$query->where('time_norm', 'like', "%{$search}%");})
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location')->label('Lokacija')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('time_norm')->label('Norma')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('number_of_workers')->label('Broj radnika')->sortable()->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('description')->label('Opis')->limit(30)->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                    ->label('Lokacija')
                    ->options([
                        'Grdica' => 'Grdica',
                        'Seovac' => 'Seovac',
                    ]),
                Tables\Filters\Filter::make('time_norm_over_60')
                    ->label('Norma veća od 60 min')
                    ->query(fn ($query) => $query->where('time_norm', '>', 60)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
