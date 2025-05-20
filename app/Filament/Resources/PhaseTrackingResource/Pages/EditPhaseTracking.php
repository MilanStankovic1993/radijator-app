<?php

namespace App\Filament\Resources\PhaseTrackingResource\Pages;

use App\Filament\Resources\PhaseTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPhaseTracking extends EditRecord
{
    protected static string $resource = PhaseTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
