<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class UserActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.user-actions-widget';

    protected int|string|array $columnSpan = 'full';

    public function getActions(): Collection
    {
        // Ovde pretpostavljamo da koristiš Laravel Auditing ili neku drugu aktivnost
        // Ako koristiš paket kao spatie/laravel-activitylog:
        return \Spatie\Activitylog\Models\Activity::causedBy(Auth::user())
            ->latest()
            ->limit(3)
            ->get();
    }
}
