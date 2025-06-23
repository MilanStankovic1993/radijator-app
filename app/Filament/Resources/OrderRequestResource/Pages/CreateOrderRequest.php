<?php

namespace App\Filament\Resources\OrderRequestResource\Pages;

use App\Filament\Resources\OrderRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderRequest extends CreateRecord
{
    protected static string $resource = OrderRequestResource::class;

    protected function beforeCreate(): void
    {
        foreach ($this->data['items'] as $item) {
            $warehouseQuantity = \App\Models\Warehouse::where('product_id', $item['product_id'])
                ->sum('quantity');

            if ($warehouseQuantity > 0) {
                $productName = \App\Models\Product::find($item['product_id'])->name ?? 'Nepoznat proizvod';
                $message = "{$warehouseQuantity} kom \"{$productName}\" već imate u magacinu.";

                \Filament\Notifications\Notification::make()
                    ->title('Provera magacina')
                    ->body($message)
                    ->warning()
                    ->send();
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
