<?php

namespace App\Filament\Resources\ArchivedWorkOrderResource\Pages;

use App\Filament\Resources\ArchivedWorkOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListArchivedWorkOrders extends ListRecords
{
    protected static string $resource = ArchivedWorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return []; // onemogućava "Create" dugme
    }
}
