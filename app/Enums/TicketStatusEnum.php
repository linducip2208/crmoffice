<?php

namespace App\Enums;

enum TicketStatusEnum: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case WaitingOnCustomer = 'waiting_on_customer';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match($this) {
            self::Open => 'Terbuka',
            self::InProgress => 'Sedang Diproses',
            self::WaitingOnCustomer => 'Menunggu Pelanggan',
            self::Resolved => 'Terselesaikan',
            self::Closed => 'Tertutup',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Open => 'danger',
            self::InProgress => 'blue',
            self::WaitingOnCustomer => 'warning',
            self::Resolved => 'success',
            self::Closed => 'gray',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Open => 'heroicon-o-ticket',
            self::InProgress => 'heroicon-o-arrow-path',
            self::WaitingOnCustomer => 'heroicon-o-clock',
            self::Resolved => 'heroicon-o-check-circle',
            self::Closed => 'heroicon-o-lock-closed',
        };
    }
}
