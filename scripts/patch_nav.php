<?php
// One-shot script — patches all Filament Admin Resource files to add navigation group/sort/recordTitleAttribute.
// Run: php scripts/patch_nav.php

$base = __DIR__ . '/../app/Filament/Admin/Resources';

$config = [
    // Resource folder => [group, sort, icon (Heroicon constant suffix), recordTitle]
    'Clients'           => ['CRM',        1,  'BuildingOffice2',         'company_name'],
    'Contacts'          => ['CRM',        2,  'Users',                   'first_name'],
    'Leads'             => ['CRM',        3,  'Sparkles',                'name'],
    'LeadStatuses'      => ['CRM',        11, 'Flag',                    'name'],
    'LeadSources'       => ['CRM',        12, 'GlobeAlt',                'name'],
    'Estimates'         => ['Sales',      1,  'DocumentText',            'number'],
    'Proposals'         => ['Sales',      2,  'Newspaper',               'number'],
    'Contracts'         => ['Sales',      3,  'DocumentCheck',           'number'],
    'Invoices'          => ['Sales',      4,  'DocumentCurrencyDollar',  'number'],
    'Payments'          => ['Sales',      5,  'Banknotes',               'transaction_id'],
    'CreditNotes'       => ['Sales',      6,  'ReceiptRefund',           'number'],
    'Expenses'          => ['Sales',      7,  'ReceiptPercent',          'description'],
    'Items'             => ['Sales',      11, 'Cube',                    'name'],
    'ExpenseCategories' => ['Sales',      12, 'Folder',                  'name'],
    'Projects'          => ['Operations', 1,  'Briefcase',               'name'],
    'Milestones'        => ['Operations', 2,  'Flag',                    'name'],
    'Tasks'             => ['Operations', 3,  'ClipboardDocumentList',   'title'],
    'TimeEntries'       => ['Operations', 4,  'Clock',                   'id'],
    'Tickets'           => ['Support',    1,  'Ticket',                  'subject'],
    'Departments'       => ['Support',    11, 'BuildingOffice',          'name'],
    'TicketPriorities'  => ['Support',    12, 'ExclamationTriangle',     'name'],
    'TicketStatuses'    => ['Support',    13, 'CheckCircle',             'name'],
    'KbCategories'      => ['Support',    21, 'FolderOpen',              'name'],
    'KbArticles'        => ['Support',    22, 'DocumentText',            'title'],
    'Users'             => ['Settings',   1,  'UserGroup',               'name'],
    'Currencies'        => ['Settings',   2,  'CurrencyDollar',          'name'],
    'TaxRates'          => ['Settings',   3,  'ReceiptPercent',          'name'],
    'Providers'         => ['Settings',   4,  'PuzzlePiece',             'name'],
];

$count = 0;
foreach ($config as $folder => [$group, $sort, $icon, $title]) {
    $resourcePath = "$base/$folder";
    if (! is_dir($resourcePath)) {
        echo "SKIP (no folder): $folder\n";
        continue;
    }

    // Find the Resource.php file (e.g., ClientResource.php)
    $files = glob("$resourcePath/*Resource.php");
    if (empty($files)) {
        echo "SKIP (no Resource.php): $folder\n";
        continue;
    }

    $path = $files[0];
    $content = file_get_contents($path);

    // If already has navigationGroup, skip
    if (str_contains($content, '$navigationGroup')) {
        echo "SKIP (already patched): $folder\n";
        continue;
    }

    // Pattern: find $navigationIcon line and inject after it
    $pattern = '/(protected static string\|BackedEnum\|null \$navigationIcon = )Heroicon::Outlined\w+;/';
    $replacement = "\$1Heroicon::Outlined$icon;\n\n    protected static ?string \$navigationLabel = '" . preg_replace('/(?<!^)(?=[A-Z])/', ' ', $folder) . "';\n\n    protected static string|\\UnitEnum|null \$navigationGroup = '$group';\n\n    protected static ?int \$navigationSort = $sort;\n\n    protected static ?string \$recordTitleAttribute = '$title';";

    $new = preg_replace($pattern, $replacement, $content, 1);
    if ($new === $content) {
        echo "WARN (no regex match): $folder\n";
        continue;
    }

    file_put_contents($path, $new);
    echo "PATCHED: $folder ($group/$sort)\n";
    $count++;
}

echo "\nDone. $count resources patched.\n";
