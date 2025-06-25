<?php

// app/Filament/Resources/CreatedTaskResource/Pages/CreateCreatedTask.php

namespace App\Filament\Resources\CreatedTaskResource\Pages;

use App\Filament\Resources\CreatedTaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCreatedTask extends CreateRecord
{
    protected static string $resource = CreatedTaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_id'] = auth()->id();

        if (!empty($data['users'])) {
            $data['users'] = collect($data['users'])
                ->reject(fn ($userId) => $userId == auth()->id())
                ->values()
                ->toArray();

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
