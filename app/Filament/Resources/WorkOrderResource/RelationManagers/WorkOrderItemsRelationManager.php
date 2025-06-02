<?php

namespace App\Filament\Resources\WorkOrderResource\RelationManagers;

use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;

class WorkOrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Proizvodnja';

    protected $listeners = ['refreshRelationManagerTable' => '$refresh'];

    public function table(Tables\Table $table): Tables\Table
{
    return $table
        ->columns([
            Tables\Columns\TextInputColumn::make('code')
                ->label('Šifra')
                ->inline()
                ->rules(['required', 'string', 'max:255'])
                ->searchable(), // omogućava pretragu po šifri

            Tables\Columns\TextColumn::make('workPhase.name')
                ->label('Faza')
                ->searchable(), // omogućava pretragu po nazivu faze

            Tables\Columns\IconColumn::make('is_confirmed')
                ->label('Potvrđeno')
                ->boolean(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('work_phase_id')
                ->label('Faza')
                ->relationship('workPhase', 'name'),

            Tables\Filters\TernaryFilter::make('is_confirmed')
                ->label('Potvrđeno'), // filtrira: da, ne, sve
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make(),
            Tables\Actions\Action::make('Potvrdi sve')
                ->action(function () {
                    $this->getRelationship()->update(['is_confirmed' => true]);
                })
                ->requiresConfirmation()
                ->color('success')
                ->icon('heroicon-o-check-circle'),
        ])
        ->actions([
            Tables\Actions\DeleteAction::make(),
            Tables\Actions\Action::make('toggleConfirmation')
                ->label(fn ($record) => $record->is_confirmed ? 'Poništi potvrdu' : 'Potvrdi')
                ->action(function ($record) {
                    $record->update(['is_confirmed' => ! $record->is_confirmed]);
                })
                ->color(fn ($record) => $record->is_confirmed ? 'danger' : 'success')
                ->icon(fn ($record) => $record->is_confirmed ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle'),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ])
        ->searchable(); // omogućava globalnu pretragu
    }

    // Obavezno mora da ima parametar Model $record da bude kompatibilno sa roditeljskom metodom
    protected function canEdit(Model $record): bool
    {
        return true;
    }
}
