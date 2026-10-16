<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RebuildSitemap extends Command
{
    protected $signature = 'crmoffice:rebuild-sitemap';

    protected $description = 'Invalidate sitemap.xml cache so it regenerates on next request.';

    public function handle(): int
    {
        // Samakan dengan key yang dipakai SitemapController:
        // CACHE_KEY_INDEX = 'sitemap_index_xml', CACHE_KEY_CHUNKS = 'sitemap_chunks_meta'.
        Cache::forget('sitemap_index_xml');
        Cache::forget('sitemap_chunks_meta');
        $this->info('Sitemap cache cleared.');

        return self::SUCCESS;
    }
}
