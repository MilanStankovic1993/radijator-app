<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_id'] = auth()->id();

        // Ukloni kreatora iz liste ako postoji više korisnika
        if (!empty($data['users'])) {
            $data['users'] = collect($data['users'])
                ->reject(fn ($userId) => $userId == auth()->id())
                ->values()
                ->toArray();

            // Ako je korisnik jedini u listi (pravi za sebe), ipak ga ostavi
            if (empty($data['users'])) {
                $data['users'] = [auth()->id()];
            }
        } else {
            $data['users'] = [auth()->id()];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->users()->sync($this->data['users'] ?? []);
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
