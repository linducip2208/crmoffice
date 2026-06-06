<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SitemapController extends Controller
{
    private const MAX_FILE_SIZE = 1_500_000;
    private const URL_OVERHEAD = 180;
    private const CACHE_KEY_INDEX = 'sitemap_index_xml';
    private const CACHE_KEY_CHUNKS = 'sitemap_chunks_meta';

    public function index(): Response
    {
        try {
            $xml = Cache::remember(self::CACHE_KEY_INDEX, 3600, fn () => $this->buildIndex());
        } catch (\Throwable $e) {
            Log::error('Sitemap index generation failed', ['error' => $e->getMessage()]);

            return response('<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>', 200, ['Content-Type' => 'application/xml; charset=utf-8']);
        }

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    public function chunk(string $file): Response
    {
        $chunks = Cache::get(self::CACHE_KEY_CHUNKS, []);

        if (isset($chunks[$file])) {
            return response($chunks[$file], 200, ['Content-Type' => 'application/xml; charset=utf-8']);
        }

        try {
            $this->regenerateChunks();
        } catch (\Throwable $e) {
            Log::error('Sitemap chunk regeneration failed', ['file' => $file, 'error' => $e->getMessage()]);
            abort(404);
        }

        $chunks = Cache::get(self::CACHE_KEY_CHUNKS, []);

        if (! isset($chunks[$file])) {
            abort(404);
        }

        return response($chunks[$file], 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    private function regenerateChunks(): void
    {
        Cache::forget(self::CACHE_KEY_INDEX);
        Cache::forget(self::CACHE_KEY_CHUNKS);
        $this->buildIndex();
    }

    private function buildIndex(): string
    {
        $urls = $this->allUrls();
        $chunks = $this->splitChunks($urls);

        $index = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $index .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $chunkMap = [];

        foreach ($chunks as $i => $chunk) {
            $filename = "sitemap-{$i}.xml";
            $chunkMap[$filename] = $this->buildChunkXml($chunk);

            $index .= "  <sitemap>\n";
            $index .= '    <loc>' . htmlspecialchars(url("/sitemap-chunk/{$filename}")) . "</loc>\n";
            $index .= '    <lastmod>' . now()->toDateString() . "</lastmod>\n";
            $index .= "  </sitemap>\n";
        }

        $index .= '</sitemapindex>';

        Cache::put(self::CACHE_KEY_CHUNKS, $chunkMap, 3600);

        return $index;
    }

    private function buildChunkXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $u) {
            $xml .= "  <url>\n    <loc>" . htmlspecialchars($u['loc']) . "</loc>\n";
            if (! empty($u['lastmod'])) {
                $xml .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
            }
            $xml .= "    <priority>" . ($u['priority'] ?? '0.5') . "</priority>\n  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    private function splitChunks(array $urls): array
    {
        $chunks = [];
        $current = [];
        $currentSize = 200;

        foreach ($urls as $url) {
            $entrySize = strlen($url['loc']) + self::URL_OVERHEAD;

            if ($currentSize + $entrySize > self::MAX_FILE_SIZE && ! empty($current)) {
                $chunks[] = $current;
                $current = [];
                $currentSize = 200;
            }

            $current[] = $url;
            $currentSize += $entrySize;
        }

        if (! empty($current)) {
            $chunks[] = $current;
        }

        return $chunks;
    }

    private function allUrls(): array
    {
        $urls = [];
        $now = now()->toDateString();

        $urls[] = ['loc' => url('/'), 'lastmod' => $now, 'priority' => '1.0'];
        $urls[] = ['loc' => url('/features'), 'priority' => '0.8'];
        $urls[] = ['loc' => url('/pricing'), 'priority' => '0.8'];
        $urls[] = ['loc' => url('/contact'), 'priority' => '0.6'];
        $urls[] = ['loc' => url('/docs'), 'priority' => '0.7'];
        $urls[] = ['loc' => url('/kb'), 'priority' => '0.7'];
        $urls[] = ['loc' => url('/blog'), 'priority' => '0.8'];

        foreach (array_keys(config('pseo.industries', [])) as $slug) {
            $urls[] = ['loc' => url("/best-crm-for-{$slug}"), 'priority' => '0.7'];
            $urls[] = ['loc' => url("/best-crm-for-{$slug}-" . now()->year), 'priority' => '0.7'];
        }

        foreach (array_keys(config('pseo.competitors', [])) as $slug) {
            $urls[] = ['loc' => url("/alternatives-to-{$slug}"), 'priority' => '0.7'];
        }

        foreach (array_keys(config('pseo.countries', [])) as $slug) {
            $urls[] = ['loc' => url("/crm-for-{$slug}"), 'priority' => '0.7'];
        }

        foreach (array_keys(config('pseo.features', [])) as $slug) {
            $urls[] = ['loc' => url("/crm-with-{$slug}"), 'priority' => '0.7'];
        }

        foreach (array_keys(config('pseo.roles', [])) as $slug) {
            $urls[] = ['loc' => url("/{$slug}-crm"), 'priority' => '0.7'];
        }

        foreach (array_keys(config('pseo.pricing-bands', [])) as $price) {
            $urls[] = ['loc' => url("/best-crm-under-{$price}"), 'priority' => '0.6'];
        }

        $industries = array_keys(config('pseo.industries', []));
        $cities = array_keys(config('pseo.cities', []));
        $sampledCities = array_slice($cities, 0, 50);

        foreach ($industries as $industry) {
            foreach ($sampledCities as $city) {
                $urls[] = ['loc' => url("/crm-for-{$industry}-in-{$city}"), 'priority' => '0.5'];
                $urls[] = ['loc' => url("/software-crm-{$industry}-{$city}"), 'priority' => '0.5'];
                $urls[] = ['loc' => url("/aplikasi-crm-{$industry}-{$city}"), 'priority' => '0.5'];
                $urls[] = ['loc' => url("/sistem-crm-{$industry}-{$city}"), 'priority' => '0.5'];
            }
        }

        $competitors = array_keys(config('pseo.competitors', []));
        foreach ($competitors as $c) {
            $urls[] = ['loc' => url("/compare/crmoffice-vs-{$c}"), 'priority' => '0.6'];
        }

        $urls[] = ['loc' => url('/beli-aplikasi-crm'), 'priority' => '0.7'];
        $urls[] = ['loc' => url('/jual-source-code-crm'), 'priority' => '0.7'];
        $urls[] = ['loc' => url('/download-source-code-crm'), 'priority' => '0.7'];

        foreach ($sampledCities as $city) {
            $urls[] = ['loc' => url("/source-code-crm-{$city}"), 'priority' => '0.5'];
        }

        foreach (array_keys(config('pseo.features', [])) as $feature) {
            $urls[] = ['loc' => url("/aplikasi-crm-{$feature}"), 'priority' => '0.5'];
        }

        foreach ($industries as $industry) {
            $urls[] = ['loc' => url("/crm-{$industry}-source-code"), 'priority' => '0.5'];
            $urls[] = ['loc' => url("/beli-source-code-crm-{$industry}"), 'priority' => '0.5'];
            $urls[] = ['loc' => url("/jual-aplikasi-crm-{$industry}"), 'priority' => '0.5'];
            foreach ($sampledCities as $city) {
                $urls[] = ['loc' => url("/source-code-crm-{$industry}-{$city}"), 'priority' => '0.4'];
            }
        }

        try {
            KbCategory::where('is_public', true)->get()->each(function ($cat) use (&$urls) {
                $urls[] = ['loc' => url("/kb/{$cat->slug}"), 'priority' => '0.6'];
            });
            KbArticle::where('is_published', true)->with('category')->get()->each(function ($art) use (&$urls) {
                if ($art->category && $art->category->is_public) {
                    $urls[] = [
                        'loc' => url("/kb/{$art->category->slug}/{$art->slug}"),
                        'lastmod' => $art->updated_at?->toDateString(),
                        'priority' => '0.5',
                    ];
                }
            });
        } catch (\Throwable) {
        }

        return $urls;
    }
}
