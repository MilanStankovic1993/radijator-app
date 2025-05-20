<?php

namespace App\Filament\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasResourcePermissions
{
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view ' . static::getResourceName());
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create ' . static::getResourceName());
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit ' . static::getResourceName());
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete ' . static::getResourceName());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    protected static function getResourceName(): string
    {
        return property_exists(static::class, 'resourceName')
            ? static::$resourceName
            : strtolower(class_basename(static::class));
    }
}
