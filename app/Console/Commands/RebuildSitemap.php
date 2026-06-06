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
        Cache::forget('sitemap.xml');
        $this->info('Sitemap cache cleared.');

        return self::SUCCESS;
    }
}
