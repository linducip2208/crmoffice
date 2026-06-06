<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Department;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        // Currencies
        foreach ([
            ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'is_base' => true, 'decimal_separator' => ',', 'thousand_separator' => '.'],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'is_base' => false, 'exchange_rate' => 0.000063],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'is_base' => false, 'exchange_rate' => 0.000058],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$', 'is_base' => false, 'exchange_rate' => 0.000084],
        ] as $c) {
            Currency::firstOrCreate(['code' => $c['code']], $c);
        }

        // Tax Rates
        foreach ([
            ['name' => 'PPN 11%', 'percentage' => 11.0000, 'is_active' => true],
            ['name' => 'PPN 12%', 'percentage' => 12.0000, 'is_active' => true],
            ['name' => 'Tax Exempt', 'percentage' => 0.0000, 'is_active' => true],
        ] as $t) {
            TaxRate::firstOrCreate(['name' => $t['name']], $t);
        }

        // Lead Sources
        foreach (['Website', 'Referral', 'Cold Outreach', 'Social Media', 'Event', 'Advertising', 'Partner', 'Other'] as $i => $name) {
            LeadSource::firstOrCreate(['name' => $name], ['is_active' => true, 'order' => $i]);
        }

        // Lead Statuses
        foreach ([
            ['name' => 'New',         'order' => 1, 'color' => '#3b82f6', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Contacted',   'order' => 2, 'color' => '#8b5cf6', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Qualified',   'order' => 3, 'color' => '#06b6d4', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Proposal',    'order' => 4, 'color' => '#eab308', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Negotiation', 'order' => 5, 'color' => '#f97316', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Won',         'order' => 6, 'color' => '#22c55e', 'is_won' => true,  'is_lost' => false],
            ['name' => 'Lost',        'order' => 7, 'color' => '#ef4444', 'is_won' => false, 'is_lost' => true],
        ] as $s) {
            LeadStatus::firstOrCreate(['name' => $s['name']], $s);
        }

        // Ticket Priorities
        foreach ([
            ['name' => 'Low',    'response_minutes_sla' => 1440, 'resolve_minutes_sla' => 10080, 'color' => '#6b7280', 'order' => 1],
            ['name' => 'Medium', 'response_minutes_sla' => 480,  'resolve_minutes_sla' => 2880,  'color' => '#3b82f6', 'order' => 2],
            ['name' => 'High',   'response_minutes_sla' => 120,  'resolve_minutes_sla' => 1440,  'color' => '#f97316', 'order' => 3],
            ['name' => 'Urgent', 'response_minutes_sla' => 60,   'resolve_minutes_sla' => 480,   'color' => '#ef4444', 'order' => 4],
        ] as $p) {
            TicketPriority::firstOrCreate(['name' => $p['name']], $p);
        }

        // Ticket Statuses
        foreach ([
            ['name' => 'Open',              'is_open' => true,  'is_resolved' => false, 'order' => 1, 'color' => '#3b82f6'],
            ['name' => 'In Progress',       'is_open' => true,  'is_resolved' => false, 'order' => 2, 'color' => '#f97316'],
            ['name' => 'Waiting Customer',  'is_open' => true,  'is_resolved' => false, 'order' => 3, 'color' => '#eab308'],
            ['name' => 'Resolved',          'is_open' => false, 'is_resolved' => true,  'order' => 4, 'color' => '#22c55e'],
            ['name' => 'Closed',            'is_open' => false, 'is_resolved' => true,  'order' => 5, 'color' => '#6b7280'],
        ] as $s) {
            TicketStatus::firstOrCreate(['name' => $s['name']], $s);
        }

        // Departments
        Department::firstOrCreate(['name' => 'Support'], ['is_active' => true]);

        // Settings (general)
        foreach ([
            ['key' => 'app_name',            'value' => 'crmoffice',    'type' => 'string', 'group' => 'general'],
            ['key' => 'app_currency',        'value' => 'IDR',          'type' => 'string', 'group' => 'general'],
            ['key' => 'app_locale',          'value' => 'id',           'type' => 'string', 'group' => 'general'],
            ['key' => 'app_timezone',        'value' => 'Asia/Jakarta', 'type' => 'string', 'group' => 'general'],
            ['key' => 'invoice_prefix',      'value' => 'INV',          'type' => 'string', 'group' => 'numbering'],
            ['key' => 'estimate_prefix',     'value' => 'EST',          'type' => 'string', 'group' => 'numbering'],
            ['key' => 'proposal_prefix',     'value' => 'PROP',         'type' => 'string', 'group' => 'numbering'],
            ['key' => 'contract_prefix',     'value' => 'CON',          'type' => 'string', 'group' => 'numbering'],
            ['key' => 'credit_note_prefix',  'value' => 'CN',           'type' => 'string', 'group' => 'numbering'],
            ['key' => 'ticket_prefix',       'value' => 'T',            'type' => 'string', 'group' => 'numbering'],
            ['key' => 'invoice_due_days',    'value' => '14',           'type' => 'int',    'group' => 'invoice'],
        ] as $s) {
            Setting::firstOrCreate(['key' => $s['key']], $s);
        }
    }
}
