<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Events\Customer\CustomerUpdated;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->check()) {
            event(new CustomerUpdated(
                auth()->user()->name,          // Ime korisnika koji menja
                auth()->id(),                  // Njegov ID
                $data['name']                  // Ime kupca koji se menja
            ));
        }

        return $data;
    }
}
