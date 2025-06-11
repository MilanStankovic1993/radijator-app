<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use Filament\Actions;
use App\Models\Product;
use App\Models\WorkOrderItem;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\WorkOrderResource\Widgets\ProductionPhasesOverview;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected static bool $isCreate = false;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Nazad')
                ->url(fn () => WorkOrderResource::getUrl('index'))
                ->color('secondary')
                ->icon('heroicon-o-arrow-left'),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getHeaderWidgets(): array
    {
        return [
            ProductionPhasesOverview::class,
        ];
    }

    protected function getHeaderWidgetData(string $widget): array
    {
        if ($widget === ProductionPhasesOverview::class) {
            return [
                'recordId' => $this->record->id,
            ];
        }

        return [];
    }

    // protected function beforeSave(): void
    // {
    //     if ($this->record->isDirty('quantity')) {
    //         $this->halt();

    //         $this->notify('warning', 'Menjate količinu!');

    //         $this->dispatchBrowserEvent('confirm-quantity-change');
    //     }
    // }

    // protected function afterSave(): void
    // {
    //     $workOrder = $this->record;

    //     // Obrisati stare stavke
    //     WorkOrderItem::where('work_order_id', $workOrder->id)->delete();

    //     $product = Product::with('workPhases')->find($workOrder->product_id);

    //     foreach ($product->workPhases as $index => $workPhase) {
    //         WorkOrderItem::create([
    //             'work_order_id' => $workOrder->id,
    //             'work_phase_id' => $workPhase->id,
    //             'product_id' => $product->id,
    //             'status' => 'pending',
    //             'is_confirmed' => false,
    //             'required_to_complete' => $workOrder->quantity,
    //         ]);
    //     }
    // }
}
