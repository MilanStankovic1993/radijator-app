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

    protected function afterSave(): void
    {
        // $workOrder = $this->record;

        // // Učitaj produkt sa radnim fazama
        // $product = Product::with('workPhases')->find($workOrder->product_id);
        // // print_r($product);
        // // die();

        // // Ako se promenila količina, možeš obrisati i ponovo napraviti stavke, ili ažurirati postojeće
        // // Ovde ćemo primer sa brisanjem i ponovnim dodavanjem:
        // WorkOrderItem::where('work_order_id', $workOrder->id)->delete();

        // $items = [];
        // $counter = 1;

        // for ($i = 0; $i < $workOrder->quantity; $i++) {
        //     foreach ($product->workPhases as $workPhase) {
        //         $items[] = [
        //             'work_order_id' => $workOrder->id,
        //             'code' => 'Stavka ' . $counter++,
        //             'work_phase_id' => $workPhase->id,
        //             'status' => 'pending',
        //             'product_id' => $product->id,
        //             'is_confirmed' => false,
        //         ];
        //     }
        // }

        // foreach ($items as $itemData) {
        //     WorkOrderItem::create($itemData);
        // }
    }
}
