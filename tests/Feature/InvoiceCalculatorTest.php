<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\TaxRate;
use App\Services\InvoiceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_recalculate_sums_items_and_tax(): void
    {
        $currency = Currency::factory()->create();
        $tax = TaxRate::factory()->create(['percentage' => 11]);
        $client = Client::factory()->create(['default_currency_id' => $currency->id]);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'discount_total' => 0,
        ]);

        $invoice->items()->createMany([
            ['description' => 'Item 1', 'quantity' => 2, 'unit_price' => 100, 'line_total' => 200, 'tax_rate_id' => $tax->id],
            ['description' => 'Item 2', 'quantity' => 1, 'unit_price' => 50,  'line_total' => 50,  'tax_rate_id' => $tax->id],
        ]);

        (new InvoiceCalculator)->recalculate($invoice);

        $invoice->refresh();
        $this->assertEquals(250.00, (float) $invoice->subtotal);
        $this->assertEquals(27.50, (float) $invoice->tax_total);
        $this->assertEquals(277.50, (float) $invoice->total);
    }
}
