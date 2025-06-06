<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use App\Models\WorkOrderItem;
use App\Models\WorkPhase;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkOrder extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\Action::make('back')
    //             ->label('Nazad')
    //             ->url(fn () => WorkOrderResource::getUrl('index'))
    //             ->color('secondary')
    //             ->icon('heroicon-o-arrow-left'),
    //     ];
    // }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $workOrder = $this->record;

        // Učitaj produkt sa radnim fazama
        $product = Product::with('workPhases')->find($workOrder->product_id);

        foreach ($product->workPhases as $index => $workPhase) {
            WorkOrderItem::create([
                'work_order_id' => $workOrder->id,
                'work_phase_id' => $workPhase->id,
                'product_id' => $product->id,
                //'code' => 'Faza ' . ($index + 1),
                'status' => 'pending',
                'is_confirmed' => false,
                'required_to_complete' => $workOrder->quantity,
            ]);
        }
    }
}
