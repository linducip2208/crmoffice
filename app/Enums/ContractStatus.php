<?php

namespace App\Enums;

enum ContractStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Signed = 'signed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Draft',
            self::Sent => 'Terkirim',
            self::Signed => 'Tertandatangani',
            self::Expired => 'Kadaluarsa',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft => 'gray',
            self::Sent => 'blue',
            self::Signed => 'success',
            self::Expired => 'warning',
            self::Cancelled => 'danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Draft => 'heroicon-o-document',
            self::Sent => 'heroicon-o-paper-airplane',
            self::Signed => 'heroicon-o-check-badge',
            self::Expired => 'heroicon-o-clock',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }
}
