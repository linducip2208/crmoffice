<?php

namespace Tests\Unit;

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

class InvoiceCalculatorUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_simple_tax_calculation(): void
    {
        $currency = Currency::factory()->create(['is_base' => true]);
        $tax = TaxRate::factory()->create(['percentage' => 10]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 0,
        ]);

        $invoice->items()->createMany([
            ['description' => 'Product A', 'quantity' => 2, 'unit_price' => 250, 'line_total' => 500, 'tax_rate_id' => $tax->id],
        ]);

        (new InvoiceCalculator)->recalculate($invoice);
        $invoice->refresh();

        $this->assertEquals(500.00, (float) $invoice->subtotal);
        $this->assertEquals(50.00, (float) $invoice->tax_total);
        $this->assertEquals(550.00, (float) $invoice->total);
    }

    public function test_multiple_tax_rates(): void
    {
        $currency = Currency::factory()->create(['is_base' => true]);
        $tax10 = TaxRate::factory()->create(['percentage' => 10]);
        $tax20 = TaxRate::factory()->create(['percentage' => 20]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 0,
        ]);

        $invoice->items()->createMany([
            ['description' => 'Service A', 'quantity' => 1, 'unit_price' => 1000, 'line_total' => 1000, 'tax_rate_id' => $tax10->id],
            ['description' => 'Service B', 'quantity' => 1, 'unit_price' => 500, 'line_total' => 500, 'tax_rate_id' => $tax20->id],
        ]);

        (new InvoiceCalculator)->recalculate($invoice);
        $invoice->refresh();

        $this->assertEquals(1500.00, (float) $invoice->subtotal);
        $this->assertEquals(200.00, (float) $invoice->tax_total);
        $this->assertEquals(1700.00, (float) $invoice->total);
    }

    public function test_item_without_tax(): void
    {
        $currency = Currency::factory()->create(['is_base' => true]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 0,
        ]);

        $invoice->items()->createMany([
            ['description' => 'Tax-free item', 'quantity' => 3, 'unit_price' => 100, 'line_total' => 300, 'tax_rate_id' => null],
        ]);

        (new InvoiceCalculator)->recalculate($invoice);
        $invoice->refresh();

        $this->assertEquals(300.00, (float) $invoice->subtotal);
        $this->assertEquals(0.00, (float) $invoice->tax_total);
        $this->assertEquals(300.00, (float) $invoice->total);
    }

    public function test_discount_application_with_tax(): void
    {
        $currency = Currency::factory()->create(['is_base' => true]);
        $tax = TaxRate::factory()->create(['percentage' => 11]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 50,
        ]);

        $invoice->items()->createMany([
            ['description' => 'Product', 'quantity' => 1, 'unit_price' => 500, 'line_total' => 500, 'tax_rate_id' => $tax->id],
        ]);

        (new InvoiceCalculator)->recalculate($invoice);
        $invoice->refresh();

        $this->assertEquals(500.00, (float) $invoice->subtotal);
        $this->assertEquals(55.00, (float) $invoice->tax_total);
        $this->assertEquals(505.00, (float) $invoice->total);
    }

    public function test_zero_amount_items(): void
    {
        $currency = Currency::factory()->create(['is_base' => true]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 0,
        ]);

        $invoice->items()->createMany([
            ['description' => 'Free item', 'quantity' => 1, 'unit_price' => 0, 'line_total' => 0, 'tax_rate_id' => null],
        ]);

        (new InvoiceCalculator)->recalculate($invoice);
        $invoice->refresh();

        $this->assertEquals(0.00, (float) $invoice->subtotal);
        $this->assertEquals(0.00, (float) $invoice->tax_total);
        $this->assertEquals(0.00, (float) $invoice->total);
    }

    public function test_status_changes_to_overdue_when_past_due_and_unpaid(): void
    {
        Event::fake([InvoicePaid::class, InvoiceOverdue::class]);

        $currency = Currency::factory()->create(['is_base' => true]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 0,
            'status' => 'sent',
            'due_date' => now()->subDays(3),
        ]);

        $invoice->items()->createMany([
            ['description' => 'Overdue item', 'quantity' => 1, 'unit_price' => 1200, 'line_total' => 1200, 'tax_rate_id' => null],
        ]);

        (new InvoiceCalculator)->recalculate($invoice);
        $invoice->refresh();

        $this->assertEquals('overdue', $invoice->status);
        $this->assertEquals(1200.00, (float) $invoice->balance_due);
    }

    public function test_status_does_not_change_when_already_paid(): void
    {
        Event::fake([InvoicePaid::class, InvoiceOverdue::class]);

        $currency = Currency::factory()->create(['is_base' => true]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 0,
            'status' => 'paid',
            'due_date' => now()->subDays(10),
        ]);

        $invoice->items()->createMany([
            ['description' => 'Paid item', 'quantity' => 1, 'unit_price' => 800, 'line_total' => 800, 'tax_rate_id' => null],
        ]);

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 800,
            'currency_id' => $currency->id,
        ]);

        (new InvoiceCalculator)->recalculate($invoice);
        $invoice->refresh();

        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(0.00, (float) $invoice->balance_due);
    }
}
