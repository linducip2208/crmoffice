<?php

return [
    'whatsapp' => '6281296052010',
    'whatsapp_link' => 'https://wa.me/6281296052010?text=Halo%2C%20saya%20tertarik%20dengan%20source%20code%20crmoffice.%20Bisa%20info%20lebih%20lanjut%3F',

    'product_name' => 'crmoffice',
    'product_tagline' => 'Self-Hosted CRM Suite — Laravel 13 + Filament 5',

    'pricing_tiers' => [
        [
            'name' => 'Basic',
            'price' => 'Rp 3.500.000',
            'description' => '1 domain license, full source code, 6 bulan support',
            'features' => [
                'Full source code (unencrypted)',
                '1 production domain',
                '6 bulan update + support',
                'Dokumentasi instalasi',
                'Free pairing license system',
            ],
        ],
        [
            'name' => 'Whitelabel',
            'price' => 'Rp 15.000.000',
            'description' => 'Unlimited domains, full source code, lifetime updates',
            'features' => [
                'Full source code (unencrypted)',
                'Unlimited production domains',
                'Resell ke client Anda',
                'Rebrand sesuka hati (nama, logo, warna)',
                'Lifetime update + priority support',
                'Free pairing license system',
                'Dapat listing di whitelabel.co.id',
                'Revenue dari resell 100% milik Anda',
            ],
        ],
        [
            'name' => 'Custom',
            'price' => 'Rp 50.000.000+',
            'description' => 'Full source code + custom development + dedicated team',
            'features' => [
                'Semua fitur Whitelabel',
                'Custom module development',
                'Integrasi khusus (ERP, POS, dll)',
                'Dedicated developer 1 bulan',
                'Migration data dari sistem lama',
                'Training + deployment support',
            ],
        ],
    ],

    'features' => [
        'Clients & Contacts Management',
        'Lead Pipeline (Kanban + Gantt)',
        'Estimates → Proposals → Contracts',
        'Recurring & Auto Invoicing',
        'Multi-Currency (IDR, USD, EUR, SGD)',
        'Payment Gateway Integration (BYOK)',
        'Expense Tracking + Receipt Upload',
        'Project Management + Time Tracking',
        'Support Tickets with SLA',
        'Customer Portal (self-service)',
        'Knowledge Base (public, SEO-indexed)',
        'Custom Fields (any module)',
        'Role & Permission (granular)',
        'Email + SMS Notification',
        '2FA Authentication',
        'Audit Trail (immutable log)',
        'REST API (Sanctum — Flutter ready)',
        'Webhook System',
        'Programmatic SEO (1M+ pages)',
        'Laravel 13 + Filament 5 + TailwindCSS',
        'Self-Hosted — own your data',
        'BYOK — bring your own keys',
    ],

    'benefits' => [
        'Kepemilikan penuh — source code 100% milik Anda, bukan sewa',
        'Self-host di server sendiri — tidak ada biaya bulanan SaaS',
        'Customizable tanpa batas — tambah fitur, ganti UI, integrasi apapun',
        'Resellable — jual ulang ke client dengan brand Anda sendiri (Whitelabel)',
        'No vendor lock-in — ganti payment gateway, AI provider, SMS provider kapan saja',
        'Modern stack — Laravel 13 + Filament 5, bukan legacy framework',
        'GDPR / UU PDP ready — data customer tetap di server Anda',
    ],

    'faqs' => [
        [
            'question' => 'Apakah ini open-source?',
            'answer' => 'Source code diberikan full dan unencrypted. Anda bisa modifikasi sesuka hati. Tapi ini bukan "open-source" dalam arti lisensi MIT/GPL — Anda membeli hak pakai + modifikasi.',
        ],
        [
            'question' => 'Bisa di-install di shared hosting?',
            'answer' => 'Ya. crmoffice berjalan di shared hosting (cPanel) selama support PHP 8.3+ dan MySQL 8.0+. Dokumentasi instalasi lengkap disertakan.',
        ],
        [
            'question' => 'Apakah ada biaya bulanan?',
            'answer' => 'Tidak. Sekali beli, pakai selamanya. Tidak ada subscription, tidak ada per-user fee, tidak ada hidden cost. Hanya one-time payment.',
        ],
        [
            'question' => 'Bagaimana cara update?',
            'answer' => 'Update source code dikirim via private GitHub repo. Anda bisa pull update dan merge dengan custom code Anda. Dokumentasi upgrade step-by-step disediakan.',
        ],
        [
            'question' => 'Bisa request custom module?',
            'answer' => 'Ya. Kami menerima custom development dengan biaya terpisah. Hubungi WhatsApp untuk diskusi kebutuhan spesifik Anda.',
        ],
        [
            'question' => 'Apakah ada demo yang bisa dicoba?',
            'answer' => 'Ya. Kunjungi halaman demo di website kami untuk mencoba semua fitur sebelum membeli.',
        ],
    ],

    'jsonld' => [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => 'crmoffice — Self-Hosted CRM Source Code',
        'description' => 'Modern self-hosted CRM source code built with Laravel 13 + Filament 5. Full source code, one-time payment, no subscription. Includes clients, leads, invoices, projects, support tickets, customer portal, KB, and programmatic SEO.',
        'sku' => 'CRM-OFFICE-SRC-1',
        'brand' => ['@type' => 'Brand', 'name' => 'crmoffice'],
        'offers' => [
            '@type' => 'AggregateOffer',
            'lowPrice' => '3500000',
            'highPrice' => '15000000',
            'priceCurrency' => 'IDR',
            'offerCount' => '3',
            'offers' => [
                [
                    '@type' => 'Offer',
                    'name' => 'Basic License',
                    'price' => '3500000',
                    'priceCurrency' => 'IDR',
                    'description' => '1 domain, full source code, 6 bulan support',
                ],
                [
                    '@type' => 'Offer',
                    'name' => 'Whitelabel License',
                    'price' => '15000000',
                    'priceCurrency' => 'IDR',
                    'description' => 'Unlimited domains, full source code, lifetime updates',
                ],
                [
                    '@type' => 'Offer',
                    'name' => 'Custom Enterprise',
                    'price' => '50000000',
                    'priceCurrency' => 'IDR',
                    'description' => 'Full source code + custom development + dedicated team',
                ],
            ],
        ],
    ],
];
