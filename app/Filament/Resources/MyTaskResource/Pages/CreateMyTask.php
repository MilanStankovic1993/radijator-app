<?php

namespace App\Filament\Resources\MyTaskResource\Pages;

use App\Filament\Resources\MyTaskResource;
use App\Filament\Resources\TaskResource\Pages\CreateTask as BaseCreateTask;

class CreateMyTask extends BaseCreateTask
{
    protected static string $resource = MyTaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_id'] = auth()->id();
        $data['users'] = [auth()->id()];
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->users()->sync([auth()->id()]);
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
