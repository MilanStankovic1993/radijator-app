<?php

namespace App\Filament\Resources\OrderRequestResource\Pages;

use App\Filament\Resources\OrderRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderRequest extends EditRecord
{
    protected static string $resource = OrderRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
    {
        if (! isset($this->data['items']) || ! is_array($this->data['items'])) {
            return;
        }

        $groupedItems = collect($this->data['items'])
            ->groupBy('product_id')
            ->map(function ($group) {
                return [
                    'product_id' => $group->first()['product_id'],
                    'quantity' => $group->sum('quantity'),
                ];
            })
            ->values()
            ->toArray();

        // Obrisati stare stavke
        $this->record->items()->delete();

        // Dodati nove grupisane
        $this->record->items()->createMany($groupedItems);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
