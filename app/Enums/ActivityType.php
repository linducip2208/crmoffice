<?php

namespace App\Enums;

enum ActivityType: string
{
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case Note = 'note';
    case Task = 'task';
    case Other = 'other';

    public function label(): string
    {
        return match($this) {
            self::Call => 'Telepon',
            self::Email => 'Email',
            self::Meeting => 'Rapat',
            self::Note => 'Catatan',
            self::Task => 'Tugas',
            self::Other => 'Lainnya',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Call => 'info',
            self::Email => 'blue',
            self::Meeting => 'purple',
            self::Note => 'gray',
            self::Task => 'success',
            self::Other => 'gray',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Call => 'heroicon-o-phone',
            self::Email => 'heroicon-o-envelope',
            self::Meeting => 'heroicon-o-user-group',
            self::Note => 'heroicon-o-pencil-square',
            self::Task => 'heroicon-o-check-circle',
            self::Other => 'heroicon-o-ellipsis-horizontal-circle',
        };
    }
}
