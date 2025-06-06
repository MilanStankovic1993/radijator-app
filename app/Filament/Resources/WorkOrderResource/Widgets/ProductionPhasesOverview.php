<?php

namespace App\Filament\Resources\WorkOrderResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\WorkOrder;
use Illuminate\Contracts\View\View;

class ProductionPhasesOverview extends Widget
{
    protected static string $view = 'filament.resources.work-order-resource.widgets.production-phases-overview';

    public ?int $recordId = null;

    public function mount(?int $recordId = null): void
    {
        $this->recordId = $recordId;
    }

    public function getData(): array
    {
        if (!$this->recordId) {
            return [];
        }

        // Ovde koristimo workPhase relaciju kao i u RelationManager-u
        $workOrder = WorkOrder::with('items.product', 'items.workPhase')->find($this->recordId);

        if (!$workOrder) {
            return [];
        }

        $data = $workOrder->items->map(function ($item) use ($workOrder) {
            return [
                'work_order' => $workOrder->work_order_number,
                'product' => $item->product->name ?? 'Nepoznato',
                'phase' => $item->workPhase->name ?? 'Nepoznato',
                'is_completed' => $item->status === 'done' ? '✅' : '❌',
                'product_id' => $item->product_id,
                'phase_id' => $item->work_phase_id ?? $item->workPhase->id ?? null, // ako ti treba
            ];
        })->toArray();

        return $data;
    }

    public function render(): View
    {
        return view(static::$view, [
            'data' => $this->getData(),
        ]);
    }
}
