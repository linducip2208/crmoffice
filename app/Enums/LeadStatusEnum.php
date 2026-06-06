<?php

namespace App\Enums;

enum LeadStatusEnum: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Proposal = 'proposal';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match($this) {
            self::New => 'Baru',
            self::Contacted => 'Dihubungi',
            self::Qualified => 'Terkualifikasi',
            self::Proposal => 'Proposal',
            self::Won => 'Menang',
            self::Lost => 'Kalah',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::New => 'info',
            self::Contacted => 'blue',
            self::Qualified => 'warning',
            self::Proposal => 'purple',
            self::Won => 'success',
            self::Lost => 'danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::New => 'heroicon-o-sparkles',
            self::Contacted => 'heroicon-o-phone',
            self::Qualified => 'heroicon-o-star',
            self::Proposal => 'heroicon-o-document-text',
            self::Won => 'heroicon-o-trophy',
            self::Lost => 'heroicon-o-x-circle',
        };
    }
}
