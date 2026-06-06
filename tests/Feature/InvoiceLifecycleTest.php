<?php

namespace Tests\Feature;

use App\Events\InvoiceOverdue;
use App\Events\InvoicePaid;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\TaxRate;
use App\Services\InvoiceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InvoiceLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_created_with_items_has_correct_total(): void
    {
        $currency = Currency::factory()->create(['is_base' => true]);
        $tax = TaxRate::factory()->create(['percentage' => 11]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 0,
        ]);

        $invoice->items()->createMany([
            ['description' => 'Design', 'quantity' => 1, 'unit_price' => 500, 'line_total' => 500, 'tax_rate_id' => $tax->id],
            ['description' => 'Development', 'quantity' => 2, 'unit_price' => 300, 'line_total' => 600, 'tax_rate_id' => $tax->id],
        ]);

        (new InvoiceCalculator)->recalculate($invoice);

        $invoice->refresh();
        $this->assertEquals(1100.00, (float) $invoice->subtotal);
        $this->assertEquals(121.00, (float) $invoice->tax_total);
        $this->assertEquals(1221.00, (float) $invoice->total);
    }

    public function test_discount_reduces_total(): void
    {
        $currency = Currency::factory()->create(['is_base' => true]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 100,
        ]);

        $invoice->items()->createMany([
            ['description' => 'Service', 'quantity' => 1, 'unit_price' => 1000, 'line_total' => 1000, 'tax_rate_id' => null],
        ]);

        (new InvoiceCalculator)->recalculate($invoice);

        $invoice->refresh();
        $this->assertEquals(1000.00, (float) $invoice->subtotal);
        $this->assertEquals(0.00, (float) $invoice->tax_total);
        $this->assertEquals(900.00, (float) $invoice->total);
    }

    public function test_partial_payment_updates_balance_due(): void
    {
        Event::fake([InvoicePaid::class, InvoiceOverdue::class]);

        $currency = Currency::factory()->create(['is_base' => true]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 0,
            'status' => 'sent',
        ]);

        $invoice->items()->createMany([
            ['description' => 'Consulting', 'quantity' => 10, 'unit_price' => 100, 'line_total' => 1000, 'tax_rate_id' => null],
        ]);

        (new InvoiceCalculator)->recalculate($invoice);

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 400,
            'currency_id' => $currency->id,
        ]);

        (new InvoiceCalculator)->recalculate($invoice);

        $invoice->refresh();
        $this->assertEquals('partial', $invoice->status);
        $this->assertEquals(400.00, (float) $invoice->paid_total);
        $this->assertEquals(600.00, (float) $invoice->balance_due);
    }

    public function test_full_payment_marks_invoice_paid(): void
    {
        Event::fake([InvoicePaid::class, InvoiceOverdue::class]);

        $currency = Currency::factory()->create(['is_base' => true]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 0,
            'status' => 'sent',
        ]);

        $invoice->items()->createMany([
            ['description' => 'Service', 'quantity' => 1, 'unit_price' => 500, 'line_total' => 500, 'tax_rate_id' => null],
        ]);

        (new InvoiceCalculator)->recalculate($invoice);

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 500,
            'currency_id' => $currency->id,
        ]);

        (new InvoiceCalculator)->recalculate($invoice);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(500.00, (float) $invoice->paid_total);
        $this->assertEquals(0.00, (float) $invoice->balance_due);
    }

    public function test_invoice_becomes_overdue_when_past_due_date(): void
    {
        Event::fake([InvoicePaid::class, InvoiceOverdue::class]);

        $currency = Currency::factory()->create(['is_base' => true]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 0,
            'status' => 'sent',
            'due_date' => now()->subDays(5),
        ]);

        $invoice->items()->createMany([
            ['description' => 'Project Phase 1', 'quantity' => 1, 'unit_price' => 800, 'line_total' => 800, 'tax_rate_id' => null],
        ]);

        (new InvoiceCalculator)->recalculate($invoice);

        $invoice->refresh();
        $this->assertEquals('overdue', $invoice->status);
        $this->assertEquals(800.00, (float) $invoice->total);
    }

    public function test_draft_status_preserved_during_recalculate(): void
    {
        Event::fake([InvoicePaid::class, InvoiceOverdue::class]);

        $currency = Currency::factory()->create(['is_base' => true]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 0,
            'status' => 'draft',
            'due_date' => now()->subDays(10),
        ]);

        $invoice->items()->createMany([
            ['description' => 'Draft item', 'quantity' => 1, 'unit_price' => 600, 'line_total' => 600, 'tax_rate_id' => null],
        ]);

        (new InvoiceCalculator)->recalculate($invoice);

        $invoice->refresh();
        $this->assertEquals('draft', $invoice->status);
        $this->assertEquals(600.00, (float) $invoice->total);
    }

    public function test_credit_note_application_tracks_pivot(): void
    {
        $currency = Currency::factory()->create(['is_base' => true]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'status' => 'sent',
            'total' => 2000,
        ]);

        $creditNote = \App\Models\CreditNote::create([
            'number' => 'CN-0001',
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'issue_date' => now(),
            'total' => 500,
            'applied_total' => 0,
            'refunded_total' => 0,
            'status' => 'open',
        ]);

        $creditNote->invoices()->attach($invoice->id, [
            'amount_applied' => 300,
            'applied_at' => now(),
        ]);

        $this->assertDatabaseHas('credit_note_invoices', [
            'invoice_id' => $invoice->id,
            'credit_note_id' => $creditNote->id,
            'amount_applied' => 300,
        ]);

        $applied = $invoice->creditNotes()->first();
        $this->assertNotNull($applied);
        $this->assertEquals(300.00, (float) $applied->pivot->amount_applied);
    }

    public function test_paid_factory_state(): void
    {
        $invoice = Invoice::factory()->paid()->create();

        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(0.00, (float) $invoice->balance_due);
        $this->assertEquals((float) $invoice->paid_total, (float) $invoice->total);
    }
}
