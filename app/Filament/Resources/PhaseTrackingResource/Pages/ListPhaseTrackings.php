<?php

namespace App\Filament\Resources\PhaseTrackingResource\Pages;

use App\Filament\Resources\PhaseTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPhaseTrackings extends ListRecords
{
    protected static string $resource = PhaseTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
