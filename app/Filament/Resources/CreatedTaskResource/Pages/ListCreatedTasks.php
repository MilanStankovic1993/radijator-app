<?php

namespace App\Filament\Resources\CreatedTaskResource\Pages;

use App\Filament\Resources\CreatedTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCreatedTasks extends ListRecords
{
    protected static string $resource = CreatedTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ->where('creator_id', auth()->id())
            ->whereHas('users', function ($query) {
                // Bar jedan korisnik koji NIJE trenutno prijavljen korisnik
                $query->where('user_id', '!=', auth()->id());
            })
            ->whereDoesntHave('users', function ($query) {
                // I nema NIJEDNOG korisnika koji jeste prijavljeni korisnik
                $query->where('user_id', auth()->id());
            });
    }

}
