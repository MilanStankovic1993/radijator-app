<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('tip_zadatka')
                ->label('Tip zadatka')
                ->options([
                    'moji' => 'Moji zadaci',
                    'kreirani' => 'Kreirani zadaci',
                ])
                ->query(function ($query, $value) {
                    if ($value === 'moji') {
                        $query->whereHas('users', fn ($q) =>
                            $q->where('user_id', auth()->id())
                        );
                    }

                    if ($value === 'kreirani') {
                        $query->where('creator_id', auth()->id());
                    }
                }),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        Task::whereHas('users', function ($q) {
            $q->where('user_id', auth()->id())->where('is_read', false);
        })->get()->each(function ($task) {
            $task->users()->updateExistingPivot(auth()->id(), ['is_read' => true]);
        });
    }
}
