<?php
namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Notifications\Notifiable;

trait HasCommonFeatures
{
    use LogsActivity, Notifiable;

    // Obavezno implementiraj ovaj metod
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // loguje sve atribute modela
            ->useLogName($this->getTable()) // ime loga po tabeli (može i fiksno)
            ->setDescriptionForEvent(fn(string $eventName) => "Model {$this->getTable()} je {$eventName}");
    }

    // možeš dodati još zajedničkih metoda ovde ako želiš
}
