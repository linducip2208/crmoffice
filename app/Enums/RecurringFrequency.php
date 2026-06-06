<?php

namespace App\Enums;

enum RecurringFrequency: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match($this) {
            self::Weekly => 'Mingguan',
            self::Monthly => 'Bulanan',
            self::Quarterly => 'Triwulan',
            self::Yearly => 'Tahunan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Weekly => 'blue',
            self::Monthly => 'green',
            self::Quarterly => 'purple',
            self::Yearly => 'orange',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Weekly => 'heroicon-o-calendar-days',
            self::Monthly => 'heroicon-o-calendar',
            self::Quarterly => 'heroicon-o-calendar-days',
            self::Yearly => 'heroicon-o-calendar-days',
        };
    }
}
