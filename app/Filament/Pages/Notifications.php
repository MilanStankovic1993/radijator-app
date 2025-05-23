<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Notifications extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static string $view = 'filament.pages.notifications';
    protected static ?string $title = 'Notifikacije';

    public function getNotifications()
    {
        return Auth::user()->notifications()->latest()->get();
    }
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
