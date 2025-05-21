<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel; // ako koristiš Laravel Excel
use Illuminate\Support\Facades\Log;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Nazad')
                ->url(fn () => ProductResource::getUrl('index'))
                ->color('secondary')
                ->icon('heroicon-o-arrow-left'),
            Actions\DeleteAction::make(), // Dodaje dugme "Delete" i u formi
            
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['import_file']) && $data['import_file'] instanceof \Illuminate\Http\UploadedFile) {
            $storedPath = $data['import_file']->store('uploads', 'public');
            $data['import_file'] = $storedPath;

            $path = Storage::disk('public')->path($storedPath);
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            switch ($extension) {
                case 'csv':
                    $rows = array_map('str_getcsv', file($path));
                    Log::info('Uvezen CSV fajl sadrži: ', $rows);
                    break;

                case 'xlsx':
                case 'xls':
                    // Ovde koristi Laravel Excel ili PhpSpreadsheet da parsiraš Excel fajl
                    Excel::import(new \App\Imports\YourImportClass, $path, 'public');
                    Log::info("Uvezen Excel fajl: " . $data['import_file']);
                    break;

                case 'json':
                    $content = file_get_contents($path);
                    $jsonData = json_decode($content, true);
                    Log::info('Uvezen JSON fajl sadrži: ', $jsonData);
                    break;

                case 'xml':
                    $content = file_get_contents($path);
                    $xml = simplexml_load_string($content);
                    Log::info('Uvezen XML fajl sadrži: ', (array) $xml);
                    break;

                case 'txt':
                    $content = file_get_contents($path);
                    Log::info('Uvezen TXT fajl sadrži: ', ['content' => $content]);
                    break;

                default:
                    Log::warning('Nepoznata ekstenzija fajla: ' . $extension);
                    break;
            }

            Notification::make()
                ->title('Uspešan import fajla!')
                ->success()
                ->send();
        }

        return $data;
    }
}
