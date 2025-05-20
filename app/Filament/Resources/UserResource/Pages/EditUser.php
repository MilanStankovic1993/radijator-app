<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Nazad')
                ->url(fn () => UserResource::getUrl('index'))
                ->color('secondary')
                ->icon('heroicon-o-arrow-left'),
            Actions\DeleteAction::make(), // Dodaje dugme "Delete" i u formi
            
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     // Ukloni permissions pre nego što ih Filament sačuva
    //     unset($data['permissions']);
    //     return $data;
    // }

    // protected function afterSave(): void
    // {
    //     if (auth()->user()->hasRole('admin')) {
    //         $this->record->syncPermissions($this->form->getState()['permissions'] ?? []);
    //     }
    // }
}
