<?php

return [
    'default_currency' => env('CRMOFFICE_DEFAULT_CURRENCY', 'IDR'),

    'numbering' => [
        'invoice_prefix' => env('CRMOFFICE_INVOICE_PREFIX', 'INV'),
        'estimate_prefix' => env('CRMOFFICE_ESTIMATE_PREFIX', 'EST'),
        'proposal_prefix' => env('CRMOFFICE_PROPOSAL_PREFIX', 'PROP'),
        'contract_prefix' => env('CRMOFFICE_CONTRACT_PREFIX', 'CON'),
        'credit_note_prefix' => env('CRMOFFICE_CREDIT_NOTE_PREFIX', 'CN'),
        'ticket_prefix' => env('CRMOFFICE_TICKET_PREFIX', 'T'),
        'reset_yearly' => true,
    ],

    'invoice' => [
        'default_due_days' => 14,
        'late_fee_percentage' => 0,
        'send_reminder_days_before_due' => [3, 1],
        'send_overdue_reminder_days_after' => [1, 7, 14, 30],
    ],

    'public_token' => [
        'length' => 40,
        'rate_limit_per_minute' => 30,
    ],

    'features' => [
        'multi_currency' => true,
        'recurring_invoices' => true,
        'time_tracking' => true,
        'kb_public' => true,
        'pseo' => true,
    ],
];
