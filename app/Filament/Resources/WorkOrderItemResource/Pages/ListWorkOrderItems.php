<?php

namespace App\Filament\Resources\WorkOrderItemResource\Pages;

use App\Filament\Resources\WorkOrderItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkOrderItems extends ListRecords
{
    protected static string $resource = WorkOrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
