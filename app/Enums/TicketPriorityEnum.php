<?php

namespace App\Enums;

enum TicketPriorityEnum: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';
    case Critical = 'critical';

    public function label(): string
    {
        return match($this) {
            self::Low => 'Rendah',
            self::Medium => 'Sedang',
            self::High => 'Tinggi',
            self::Urgent => 'Mendesak',
            self::Critical => 'Kritis',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Low => 'gray',
            self::Medium => 'info',
            self::High => 'warning',
            self::Urgent => 'danger',
            self::Critical => 'danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Low => 'heroicon-o-arrow-down',
            self::Medium => 'heroicon-o-minus',
            self::High => 'heroicon-o-arrow-up',
            self::Urgent => 'heroicon-o-exclamation-triangle',
            self::Critical => 'heroicon-o-fire',
        };
    }
}
