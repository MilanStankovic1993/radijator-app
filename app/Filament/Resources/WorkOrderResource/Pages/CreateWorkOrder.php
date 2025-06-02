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
        
            $workOrder = $this->record; // Novo kreirani radni nalog

            // Učitaj produkt sa radnim fazama
            $product = Product::with('workPhases')->find($workOrder->product_id);
            
                $test = [];
                $counter = 1; // Za brojanje stavki

                for ($i = 0; $i < $workOrder->quantity; $i++) {
                    foreach ($product->workPhases as $workPhase) {
                        $test[] = [
                            'work_order_id' => $workOrder->id,
                            'code' => 'Stavka ' . $counter++,
                            'work_phase_id' => $workPhase->id,
                            'status' => 'pending',
                            'product_id' => $product->id,
                            'is_confirmed' => false,
                        ];
                    }
                }
                // dd($test);

                // Ako želiš da odmah snimiš sve stavke u bazu:
                foreach ($test as $itemData) {
                    WorkOrderItem::create($itemData);
                }

                // ILI ako samo želiš da vidiš rezultat:
                // dd($test);
            }

}
