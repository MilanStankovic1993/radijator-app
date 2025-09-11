<?php

namespace App\Filament\Resources\OrderRequestResource\Pages;

use App\Filament\Resources\OrderRequestResource;
use App\Models\Product;
use App\Models\Warehouse;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderRequest extends CreateRecord
{
    protected static string $resource = OrderRequestResource::class;

    /**
     * Pre nego što se model kreira:
     * - spojimo stavke sa istim product_id i saberemo quantity
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! isset($data['items']) || ! is_array($data['items'])) {
            return $data;
        }

        // Filtriraj polomljene unose i normalizuj quantity
        $normalized = collect($data['items'])
            ->filter(fn ($row) => isset($row['product_id']) && $row['product_id'])
            ->map(function ($row) {
                $row['quantity'] = (int) ($row['quantity'] ?? 0);
                return $row;
            });

        // Grupisanje po product_id + sumiranje quantity
        $data['items'] = $normalized
            ->groupBy('product_id')
            ->map(fn ($group) => [
                'product_id' => $group->first()['product_id'],
                'quantity'   => $group->sum('quantity'),
            ])
            ->values()
            ->all();

        return $data;
    }

    /**
     * Posle mutacije podataka, a pre kreiranja:
     * - samo prikažemo upozorenje ako već ima na lageru (ne diramo snimanje)
     */
    protected function beforeCreate(): void
    {
        if (! isset($this->data['items']) || ! is_array($this->data['items'])) {
            return;
        }

        foreach ($this->data['items'] as $item) {
            $productId = $item['product_id'] ?? null;
            if (! $productId) {
                continue;
            }

            $warehouseQuantity = Warehouse::where('product_id', $productId)->sum('quantity');
            if ($warehouseQuantity > 0) {
                $productName = Product::find($productId)->name ?? 'Nepoznat proizvod';

                Notification::make()
                    ->title('Provera magacina')
                    ->body("{$warehouseQuantity} kom \"{$productName}\" već imate u magacinu.")
                    ->warning()
                    ->send();
            }
        }
    }

    /**
     * Ne radimo ručno snimanje stavki ovde!
     * Filament će kroz Repeater ->relationship('items') sam upisati u pivot/tabelu.
     */
    protected function afterCreate(): void
    {
        // Namerno prazno – izbeći duplo kreiranje stavki.
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
