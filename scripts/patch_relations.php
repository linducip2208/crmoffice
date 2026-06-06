<?php
// Register relation managers into each Resource's getRelations() method.

$base = __DIR__ . '/../app/Filament/Admin/Resources';

$config = [
    'Clients/ClientResource.php' => [
        'ContactsRelationManager',
        'ActivitiesRelationManager',
        'NotesRelationManager',
        'InvoicesRelationManager',
        'ProjectsRelationManager',
        'TicketsRelationManager',
    ],
    'Leads/LeadResource.php' => [
        'ActivitiesRelationManager',
        'NotesRelationManager',
    ],
    'Projects/ProjectResource.php' => [
        'MilestonesRelationManager',
        'TasksRelationManager',
        'TimeEntriesRelationManager',
        'InvoicesRelationManager',
        'ExpensesRelationManager',
    ],
    'Tasks/TaskResource.php' => [
        'TimeEntriesRelationManager',
    ],
    'Tickets/TicketResource.php' => [
        'RepliesRelationManager',
    ],
    'Invoices/InvoiceResource.php' => [
        'PaymentsRelationManager',
    ],
];

$count = 0;
foreach ($config as $relPath => $managers) {
    $path = "$base/$relPath";
    if (! file_exists($path)) {
        echo "SKIP (missing): $relPath\n";
        continue;
    }

    $content = file_get_contents($path);

    // Build the relation manager class references
    $classes = array_map(fn ($m) => "            \\App\\Filament\\Admin\\Resources\\" . str_replace('.php', '', dirname($relPath)) . "\\RelationManagers\\$m::class,", $managers);
    $body = "        return [\n" . implode("\n", $classes) . "\n        ];";

    // Replace existing getRelations() body
    $pattern = '/(public static function getRelations\(\): array\s*\{\s*)return \[[^\]]*\];/s';
    $new = preg_replace($pattern, "\$1$body", $content, 1);

    if ($new === $content) {
        echo "WARN (regex no match): $relPath\n";
        continue;
    }

    file_put_contents($path, $new);
    echo "PATCHED: $relPath (" . count($managers) . " managers)\n";
    $count++;
}

echo "\nDone. $count resources updated.\n";
