<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use App\Models\WorkOrderItem;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkOrder extends CreateRecord
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
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        for ($i = 0; $i < $this->record->quantity; $i++) {
            WorkOrderItem::create([
                'work_order_id' => $this->record->id,
                'name' => 'Stavka ' . ($i + 1),
            ]);
        }
    }

}
