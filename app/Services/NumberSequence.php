<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NumberSequence
{
    private const PREFIX_MAP = [
        'invoice' => 'invoice_prefix',
        'estimate' => 'estimate_prefix',
        'proposal' => 'proposal_prefix',
        'contract' => 'contract_prefix',
        'credit_note' => 'credit_note_prefix',
        'ticket' => 'ticket_prefix',
    ];

    public static function peek(string $key): string
    {
        return self::format($key, self::currentValue($key) + 1);
    }

    public static function next(string $key): string
    {
        return DB::transaction(function () use ($key) {
            $year = self::yearForKey($key);
            $row = DB::table('number_sequences')
                ->where('key', $key)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($row) {
                $next = $row->current + 1;
                DB::table('number_sequences')
                    ->where('id', $row->id)
                    ->update(['current' => $next, 'updated_at' => now()]);
            } else {
                $next = 1;
                DB::table('number_sequences')->insert([
                    'key' => $key,
                    'year' => $year,
                    'current' => $next,
                    'updated_at' => now(),
                ]);
            }

            return self::format($key, $next);
        });
    }

    private static function currentValue(string $key): int
    {
        $year = self::yearForKey($key);

        return (int) DB::table('number_sequences')
            ->where('key', $key)
            ->where('year', $year)
            ->value('current');
    }

    private static function format(string $key, int $value): string
    {
        $prefix = self::prefix($key);
        $year = self::yearForKey($key);
        $padded = str_pad((string) $value, 4, '0', STR_PAD_LEFT);

        return $year
            ? "{$prefix}-{$year}-{$padded}"
            : "{$prefix}-{$padded}";
    }

    private static function prefix(string $key): string
    {
        $settingKey = self::PREFIX_MAP[$key] ?? null;
        if ($settingKey) {
            $value = \App\Models\Setting::get($settingKey);
            if ($value) {
                return $value;
            }
        }

        return strtoupper(substr($key, 0, 3));
    }

    private static function yearForKey(string $key): ?int
    {
        $resetYearly = config('crmoffice.numbering.reset_yearly', true);
        // Tickets are continuous (no year prefix)
        if ($key === 'ticket') {
            return null;
        }

        return $resetYearly ? (int) now()->year : null;
    }
}
