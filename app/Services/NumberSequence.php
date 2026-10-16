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

            // Pastikan row sequence ada. insertOrIgnore atomik terhadap unique
            // index (key, year): dua request bersamaan tidak bisa membuat dua
            // row — unique index yang menahan, bukan SELECT ... lockForUpdate
            // (lock tidak bekerja saat row belum ada).
            DB::table('number_sequences')->insertOrIgnore([
                'key' => $key,
                'year' => $year,
                'current' => 0,
                'updated_at' => now(),
            ]);

            $row = DB::table('number_sequences')
                ->where('key', $key)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            $next = ((int) $row->current) + 1;

            DB::table('number_sequences')
                ->where('id', $row->id)
                ->update(['current' => $next, 'updated_at' => now()]);

            return self::format($key, $next);
        }, 3);
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

        return $year > 0
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

    /**
     * Tahun untuk sequence. 0 = sequence kontinu tanpa reset (mis. tiket).
     * Jangan pernah mengembalikan null: unique index (key, year) tidak
     * menganggap dua NULL sebagai duplikat di MySQL — celah nomor ganda.
     */
    private static function yearForKey(string $key): int
    {
        // Tiket kontinu (tanpa prefix tahun).
        if ($key === 'ticket') {
            return 0;
        }

        $resetYearly = config('crmoffice.numbering.reset_yearly', true);

        return $resetYearly ? (int) now()->year : 0;
    }
}
