<?php

namespace App\Filament\Resources\WorkOrderResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\WorkOrder;
use Illuminate\Contracts\View\View;

class ProductionPhasesOverview extends Widget
{
    protected static string $view = 'filament.resources.work-order-resource.widgets.production-phases-overview';

    public ?int $recordId = null; // nullable, da Livewire ne puca

    // Mount sa nullable parametrom i default null
    public function mount(?int $recordId = null): void
    {
        $this->recordId = $recordId;
    }

    public function getData(): array
    {
        if (!$this->recordId) {
            return [];
        }

        $workOrder = WorkOrder::with('items.product', 'items.phase')->find($this->recordId);

        if (!$workOrder) {
            return [];
        }

        $data = $workOrder->items->map(function ($item) use ($workOrder) {
            return [
                'work_order' => $workOrder->work_order_number,
                'product' => $item->product->name ?? 'Nepoznato',
                'phase' => $item->phase->name ?? 'Nepoznato',
                'is_completed' => $item->status === 'done' ? '✅' : '❌',
                'product_id' => $item->product_id,
                'phase_id' => $item->phase_id,
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
