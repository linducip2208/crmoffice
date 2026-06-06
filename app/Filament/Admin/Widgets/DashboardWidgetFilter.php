<?php

namespace App\Filament\Admin\Widgets;

trait DashboardWidgetFilter
{
    public static function canView(): bool
    {
        return static::isVisibleToRole(auth()->user()?->getRoleNames()->toArray() ?? []);
    }

    protected static function isVisibleToRole(array $roles): bool
    {
        return true;
    }
}
