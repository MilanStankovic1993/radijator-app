<?php

namespace App\Filament\Resources\TaskResource\Pages;

use Filament\Resources\Pages\ViewRecord;

class ViewTask extends ViewRecord
{
    protected static string $resource = \App\Filament\Resources\TaskResource::class;

    protected static string $view = 'filament.resources.task-resource.view';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->users()->updateExistingPivot(auth()->id(), ['is_read' => true]);
        $this->record->updateStatus();
        return $data;
    }

    protected function getViewData(): array
    {
        return [
            'record' => $this->record,
        ];
    }
}
