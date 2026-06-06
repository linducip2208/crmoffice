<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class PseoAudit extends Command
{
    protected $signature = 'pseo:audit {--limit=20 : Number of pages to sample} {--base= : Override base URL (default APP_URL)}';

    protected $description = 'Sample pSEO pages and check status, word count, JSON-LD, canonical.';

    public function handle(): int
    {
        $base = rtrim($this->option('base') ?: config('app.url'), '/');
        $limit = (int) $this->option('limit');

        $routes = [];
        foreach (array_keys(config('pseo.industries', [])) as $slug) {
            $routes[] = "/best-crm-for-$slug";
        }
        foreach (array_keys(config('pseo.competitors', [])) as $slug) {
            $routes[] = "/alternatives-to-$slug";
        }
        foreach (array_keys(config('pseo.countries', [])) as $slug) {
            $routes[] = "/crm-for-$slug";
        }
        foreach (array_keys(config('pseo.features', [])) as $slug) {
            $routes[] = "/crm-with-$slug";
        }
        foreach (array_keys(config('pseo.roles', [])) as $slug) {
            $routes[] = "/$slug-crm";
        }

        $sampled = array_slice($routes, 0, $limit);
        $issues = 0;

        foreach ($sampled as $path) {
            $url = $base.$path;
            $response = Http::timeout(10)->get($url);
            $body = $response->body();

            $words = str_word_count(strip_tags($body));
            $hasJsonLd = str_contains($body, 'application/ld+json');
            $hasCanonical = str_contains($body, 'rel="canonical"');
            $status = $response->status();

            $ok = $status === 200 && $words >= 300 && $hasCanonical;
            $marker = $ok ? '<info>OK</info>' : '<error>FAIL</error>';
            if (! $ok) {
                $issues++;
            }

            $this->line(sprintf(
                '%s %s — status=%d words=%d jsonld=%s canonical=%s',
                $marker, $path, $status, $words,
                $hasJsonLd ? 'yes' : 'no',
                $hasCanonical ? 'yes' : 'no'
            ));
        }

        $this->newLine();
        $this->info("Audited ".count($sampled)." routes — $issues issue(s).");

        return $issues > 0 ? self::FAILURE : self::SUCCESS;
    }
}
