<?php

namespace App\Filament\Resources\MyTaskResource\Pages;

use App\Filament\Resources\MyTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyTasks extends ListRecords
{
    protected static string $resource = MyTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
