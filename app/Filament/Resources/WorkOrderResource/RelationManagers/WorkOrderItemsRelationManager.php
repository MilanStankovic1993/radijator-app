<?php

namespace App\Filament\Resources\WorkOrderResource\RelationManagers;

use Filament\Tables;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;

class WorkOrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Proizvodnja';

    protected $listeners = ['refreshRelationManagerTable' => '$refresh'];

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Šifra')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('work_phase_id')
                    ->label('Faza')
                    ->relationship('workPhase', 'name')
                    ->required(),

                Forms\Components\Toggle::make('is_confirmed')
                    ->label('Potvrđeno')
                    ->default(false),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextInputColumn::make('code')
                    ->label('Šifra')
                    ->inline()
                    ->rules(['required', 'string', 'max:255'])
                    ->searchable(),

                Tables\Columns\TextColumn::make('workPhase.name')
                    ->label('Faza')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_confirmed')
                    ->label('Potvrđeno')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('work_phase_id')
                    ->label('Faza')
                    ->relationship('workPhase', 'name'),

                Tables\Filters\TernaryFilter::make('is_confirmed')
                    ->label('Potvrđeno'),
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
            ->searchable();
    }

    protected function canEdit(Model $record): bool
    {
        return true;
    }

}
