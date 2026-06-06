<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Todo => 'To Do',
            self::InProgress => 'Sedang Dikerjakan',
            self::Review => 'Review',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Todo => 'gray',
            self::InProgress => 'blue',
            self::Review => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Todo => 'heroicon-o-check-circle',
            self::InProgress => 'heroicon-o-arrow-path',
            self::Review => 'heroicon-o-eye',
            self::Completed => 'heroicon-o-check-badge',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }
}
