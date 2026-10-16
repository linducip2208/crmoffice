<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * MySQL tidak menganggap dua NULL sebagai duplikat pada unique index,
     * sehingga unique (key, year) tidak menahan baris ganda untuk sequence
     * ber-tahun NULL (mis. tiket). Kita pindahkan semua NULL ke 0 agar
     * unique index benar-benar berlaku.
     */
    public function up(): void
    {
        DB::table('number_sequences')->whereNull('year')->update(['year' => 0]);
    }

    public function down(): void
    {
        // Backfill data tidak dapat dibalik; tidak ada perubahan skema.
    }
};
