<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use App\Filament\Resources\WorkOrderResource\Widgets\ProductionPhasesOverview;

class ViewWorkOrder extends ViewRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductionPhasesOverview::class,
        ];
    }

    protected function getHeaderWidgetData(string $widget): array
    {
        if ($widget === ProductionPhasesOverview::class) {
            return [
                'recordId' => $this->record->id,
            ];
        }
        return [];
    }
}
