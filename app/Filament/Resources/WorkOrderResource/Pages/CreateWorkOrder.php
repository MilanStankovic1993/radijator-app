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

    /**
     * Get the URL to redirect to after creation.
     *
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Automatsko dodaje stavke radnog naloga ako tip nije custom.
     *
     * Kada se kreira novi radni nalog, a tip nije custom, ovaj metoda će
     * automatski dodati sve faze rada koji pripadaju proizvodu koji je
     * vezan za radni nalog.
     */
    protected function afterCreate(): void
    {
        $workOrder = $this->record;

        // Ako je tip custom, preskoči automatsko dodavanje stavki
        if ($workOrder->type === 'custom') {
            return;
        }

        $product = Product::with('workPhases')->find($workOrder->product_id);

        foreach ($product->workPhases as $workPhase) {
            WorkOrderItem::create([
                'work_order_id' => $workOrder->id,
                'work_phase_id' => $workPhase->id,
                'product_id' => $product->id,
                'status' => 'pending',
                'is_confirmed' => false,
                'required_to_complete' => $workOrder->quantity,
            ]);
        }
    }
}
