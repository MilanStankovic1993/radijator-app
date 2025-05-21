<?php

namespace App\Filament\Resources\ProductionTrackingResource\Pages;

use App\Filament\Resources\ProductionTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductionTracking extends EditRecord
{
    protected static string $resource = ProductionTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
