<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Checkbox;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->load('users');
        $this->record->users()->updateExistingPivot(auth()->id(), ['is_read' => true]);
        return $data;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')->label('Naslov zadatka')->required(),
            Textarea::make('description')->label('Opis zadatka'),
            Select::make('users')
                ->label('Dodaj korisnike')
                ->multiple()
                ->relationship('users', 'name')
                ->preload()
                ->required(),
            DatePicker::make('due_date')->label('Rok za završetak'),

            Checkbox::make('pivot_is_done')
                ->label('Označi kao odrađen')
                ->visible(fn () => $this->record && $this->record->users->contains(auth()->id()))
                ->default(fn () => $this->record->users->firstWhere('id', auth()->id())?->pivot->is_done)
                ->afterStateUpdated(function ($state) {
                    $this->record->users()->updateExistingPivot(auth()->id(), [
                        'is_done' => $state,
                    ]);
                    $this->record->updateStatus();
                }),
        ]);
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
