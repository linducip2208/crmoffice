<?php

use App\Models\Client;
use App\Models\CreditNote;
use App\Models\Currency;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceCalculator;
use Illuminate\Support\Facades\Event;

test('sales flow: create estimate with line items', function () {
    Currency::factory()->create(['code' => 'IDR', 'is_base' => true]);
    $client = Client::factory()->create();

    $estimate = Estimate::factory()->create([
        'client_id' => $client->id,
        'status' => 'draft',
        'subtotal' => 1500,
        'tax_total' => 165,
        'discount_total' => 0,
        'total' => 1665,
    ]);

    $estimate->items()->createMany([
        ['description' => 'Web Design', 'quantity' => 1, 'unit_price' => 1000, 'line_total' => 1000],
        ['description' => 'Logo Design', 'quantity' => 1, 'unit_price' => 500, 'line_total' => 500],
    ]);

    expect($estimate->items()->count())->toBe(2);
    expect((float) $estimate->total)->toBe(1665.00);
});

test('sales flow: convert estimate to invoice', function () {
    Currency::factory()->create(['code' => 'IDR', 'is_base' => true]);
    $client = Client::factory()->create();

    $estimate = Estimate::factory()->create([
        'client_id' => $client->id,
        'status' => 'sent',
        'subtotal' => 2000,
        'discount_total' => 0,
        'tax_total' => 0,
        'total' => 2000,
    ]);

    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'estimate_id' => $estimate->id,
        'status' => 'draft',
        'subtotal' => 2000,
        'discount_total' => 0,
        'tax_total' => 0,
        'total' => 2000,
        'balance_due' => 2000,
    ]);

    $estimate->update([
        'status' => 'accepted',
        'converted_invoice_id' => $invoice->id,
    ]);

    $estimate->refresh();
    expect($estimate->status)->toBe('accepted');
    expect($estimate->converted_invoice_id)->toBe($invoice->id);

    $invoice->refresh();
    expect($invoice->estimate_id)->toBe($estimate->id);
});

test('sales flow: create recurring invoice with recurrence config', function () {
    Currency::factory()->create(['code' => 'IDR', 'is_base' => true]);
    $client = Client::factory()->create();

    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'status' => 'draft',
        'is_recurring' => true,
        'recurring_period' => 'monthly',
        'recurring_count' => 12,
        'recurring_remaining' => 12,
        'next_recurring_date' => now()->addMonth(),
        'total' => 500,
    ]);

    expect($invoice->is_recurring)->toBeTrue();
    expect($invoice->recurring_period)->toBe('monthly');
    expect($invoice->recurring_count)->toBe(12);
    expect($invoice->recurring_remaining)->toBe(12);
    expect($invoice->next_recurring_date)->not->toBeNull();
});

test('sales flow: record payment and verify invoice status updated', function () {
    Event::fake();

    $currency = Currency::factory()->create(['is_base' => true]);
    $client = Client::factory()->create(['default_currency_id' => $currency->id]);

    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'currency_id' => $currency->id,
        'status' => 'sent',
        'subtotal' => 1000,
        'discount_total' => 0,
        'tax_total' => 0,
        'total' => 1000,
        'paid_total' => 0,
        'balance_due' => 1000,
    ]);

    $invoice->items()->createMany([
        ['description' => 'Service', 'quantity' => 1, 'unit_price' => 1000, 'line_total' => 1000],
    ]);

    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 1000,
        'currency_id' => $currency->id,
    ]);

    (new InvoiceCalculator)->recalculate($invoice);
    $invoice->refresh();

    expect($invoice->status)->toBe('paid');
    expect((float) $invoice->paid_total)->toBe(1000.00);
    expect((float) $invoice->balance_due)->toBe(0.00);
});

test('sales flow: create credit note and apply to invoice', function () {
    $currency = Currency::factory()->create(['is_base' => true]);
    $client = Client::factory()->create(['default_currency_id' => $currency->id]);

    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'currency_id' => $currency->id,
        'status' => 'sent',
        'total' => 2000,
    ]);

    $creditNote = CreditNote::create([
        'number' => 'CN-00042',
        'client_id' => $client->id,
        'currency_id' => $currency->id,
        'issue_date' => now(),
        'total' => 500,
        'applied_total' => 0,
        'refunded_total' => 0,
        'status' => 'open',
    ]);

    $creditNote->invoices()->attach($invoice->id, [
        'amount_applied' => 500,
        'applied_at' => now(),
    ]);

    expect($invoice->creditNotes()->count())->toBe(1);
    expect((float) $invoice->creditNotes()->first()->pivot->amount_applied)->toBe(500.00);

    $this->assertDatabaseHas('credit_note_invoices', [
        'invoice_id' => $invoice->id,
        'credit_note_id' => $creditNote->id,
        'amount_applied' => 500,
    ]);
});

test('sales flow: partial payment updates balance correctly', function () {
    Event::fake();

    $currency = Currency::factory()->create(['is_base' => true]);
    $client = Client::factory()->create(['default_currency_id' => $currency->id]);

    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'currency_id' => $currency->id,
        'status' => 'sent',
        'subtotal' => 3000,
        'discount_total' => 0,
        'tax_total' => 0,
        'total' => 3000,
        'paid_total' => 0,
        'balance_due' => 3000,
    ]);

    $invoice->items()->createMany([
        ['description' => 'Consulting', 'quantity' => 1, 'unit_price' => 3000, 'line_total' => 3000],
    ]);

    Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 1000,
        'currency_id' => $currency->id,
    ]);

    (new InvoiceCalculator)->recalculate($invoice);
    $invoice->refresh();

    expect($invoice->status)->toBe('partial');
    expect((float) $invoice->paid_total)->toBe(1000.00);
    expect((float) $invoice->balance_due)->toBe(2000.00);
});
