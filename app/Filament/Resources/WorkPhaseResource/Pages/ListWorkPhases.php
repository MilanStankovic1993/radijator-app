<?php

namespace App\Filament\Resources\WorkPhaseResource\Pages;

use App\Filament\Resources\WorkPhaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkPhases extends ListRecords
{
    protected static string $resource = WorkPhaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
