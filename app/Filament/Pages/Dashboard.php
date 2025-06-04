<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\UserActionsWidget;
use App\Filament\Resources\DashboardResource\Widgets\BlogPostsChart;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';  // Ikonica kad nisi na stranici
    protected static ?string $navigationActiveIcon = 'heroicon-s-home';  // (Ovo možda ne postoji u Filamentu, možeš ukloniti ili rešiti drugačije)

    // protected static ?string $navigationLabel = 'Početna';   // Evo ovde je ispravljeno
    protected static ?int $navigationSort = -1;

    protected static string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            BlogPostsChart::class,
        ];
    }
    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         UserActionsWidget::class,
    //     ];
    // }
}
