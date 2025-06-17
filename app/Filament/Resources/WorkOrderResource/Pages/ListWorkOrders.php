<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkOrders extends ListRecords
{
    protected static string $resource = WorkOrderResource::class;

    /**
     * Actions to display in the header of the list page.
     *
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('customCreate')
                ->label('🛠 Kreiraj Custom RN')
                ->url(fn () => CustomCreateWorkOrder::getUrl())
                ->color('gray'),
            Actions\CreateAction::make(),
        ];
    }
}
