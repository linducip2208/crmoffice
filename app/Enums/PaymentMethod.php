<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case CreditCard = 'credit_card';
    case Cash = 'cash';
    case Ewallet = 'ewallet';
    case Qris = 'qris';

    public function label(): string
    {
        return match($this) {
            self::BankTransfer => 'Transfer Bank',
            self::CreditCard => 'Kartu Kredit',
            self::Cash => 'Tunai',
            self::Ewallet => 'E-Wallet',
            self::Qris => 'QRIS',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::BankTransfer => 'blue',
            self::CreditCard => 'purple',
            self::Cash => 'green',
            self::Ewallet => 'orange',
            self::Qris => 'teal',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::BankTransfer => 'heroicon-o-building-library',
            self::CreditCard => 'heroicon-o-credit-card',
            self::Cash => 'heroicon-o-banknotes',
            self::Ewallet => 'heroicon-o-device-phone-mobile',
            self::Qris => 'heroicon-o-qr-code',
        };
    }
}
