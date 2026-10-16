<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\NumberSequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NumberSequenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class);

        // Prefix produksi hasil ReferenceDataSeeder.
        Setting::set('ticket_prefix', 'T');
    }

    public function test_ticket_sequence_increments_and_formats_like_production(): void
    {
        $this->assertSame('T-0001', NumberSequence::next('ticket'));
        $this->assertSame('T-0002', NumberSequence::next('ticket'));
    }

    public function test_sequence_row_uses_zero_instead_of_null_year(): void
    {
        NumberSequence::next('ticket');

        // NULL tidak dianggap duplikat oleh unique index MySQL — pastikan 0.
        $this->assertSame(0, (int) DB::table('number_sequences')
            ->where('key', 'ticket')
            ->value('year'));

        $this->assertDatabaseCount('number_sequences', 1);
    }

    public function test_peek_does_not_consume_a_number(): void
    {
        $this->assertSame('T-0001', NumberSequence::peek('ticket'));

        NumberSequence::next('ticket');

        $this->assertSame('T-0002', NumberSequence::peek('ticket'));
        $this->assertSame('T-0002', NumberSequence::next('ticket'));
    }
}
