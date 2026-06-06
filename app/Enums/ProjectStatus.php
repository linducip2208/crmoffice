<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Planning = 'planning';
    case Active = 'active';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Planning => 'Perencanaan',
            self::Active => 'Aktif',
            self::OnHold => 'Ditunda',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Planning => 'blue',
            self::Active => 'info',
            self::OnHold => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Planning => 'heroicon-o-clipboard-document-list',
            self::Active => 'heroicon-o-play-circle',
            self::OnHold => 'heroicon-o-pause-circle',
            self::Completed => 'heroicon-o-check-circle',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }
}
