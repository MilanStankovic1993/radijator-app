<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use Filament\Actions;
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
        $record = $this->record;

        $existingItems = $record->items()->get();
        $existingCount = $existingItems->count();
        $newCount = $record->quantity;

        if ($newCount > $existingCount) {
            for ($i = $existingCount; $i < $newCount; $i++) {
                \App\Models\WorkOrderItem::create([
                    'work_order_id' => $record->id,
                    'name' => 'Stavka ' . ($i + 1),
                ]);
            }
        } elseif ($newCount < $existingCount) {
            $record->items()->delete();

            for ($i = 0; $i < $newCount; $i++) {
                \App\Models\WorkOrderItem::create([
                    'work_order_id' => $record->id,
                    'name' => 'Stavka ' . ($i + 1),
                ]);
            }
        }
    }
}
