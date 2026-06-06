<?php

namespace App\Enums;

enum SlaSeverity: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match($this) {
            self::Low => 'Rendah',
            self::Medium => 'Sedang',
            self::High => 'Tinggi',
            self::Critical => 'Kritis',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Low => 'info',
            self::Medium => 'success',
            self::High => 'warning',
            self::Critical => 'danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Low => 'heroicon-o-arrow-down',
            self::Medium => 'heroicon-o-minus',
            self::High => 'heroicon-o-arrow-up',
            self::Critical => 'heroicon-o-exclamation-triangle',
        };
    }
}
