<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Expired = 'expired';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Draft',
            self::Sent => 'Terkirim',
            self::Accepted => 'Disetujui',
            self::Declined => 'Ditolak',
            self::Expired => 'Kadaluarsa',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft => 'gray',
            self::Sent => 'blue',
            self::Accepted => 'success',
            self::Declined => 'danger',
            self::Expired => 'warning',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Draft => 'heroicon-o-document',
            self::Sent => 'heroicon-o-paper-airplane',
            self::Accepted => 'heroicon-o-check-circle',
            self::Declined => 'heroicon-o-x-circle',
            self::Expired => 'heroicon-o-clock',
        };
    }
}
