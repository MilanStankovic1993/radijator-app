<?php

namespace App\Filament\Resources\EmployeesResource\Pages;

use App\Filament\Resources\EmployeesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployees extends CreateRecord
{
    protected static string $resource = EmployeesResource::class;

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
            case 'jpg':
            case 'jpeg':
            case 'png':                
                $imageInfo = getimagesize($path);
                Log::info('Uvezen fajl:', [
                    'mime' => $imageInfo['mime'] ?? 'unknown',
                    'width' => $imageInfo[0] ?? 'unknown',
                    'height' => $imageInfo[1] ?? 'unknown',
                    'path' => $storedPath,
                ]);
                break;

                default:
                    Log::warning('Nepoznata ekstenzija fajla: ' . $extension);
                    break;
            }

            FilamentNotification::make()
                ->title('Uspešan import fajla!')
                ->success()
                ->send();
        }

        return $data;
    }
}
