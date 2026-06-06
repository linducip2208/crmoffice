<?php

namespace App\Console\Commands\Seo;

use App\Models\BlogPost;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Services\Seo\IndexNowService;
use Illuminate\Console\Command;

class IndexNowSubmit extends Command
{
    protected $signature = 'seo:indexnow
                            {--url= : Submit a single URL}
                            {--new : Submit only new URLs since last run}
                            {--all : Submit all sitemap URLs}';
    protected $description = 'Submit URLs to IndexNow (Bing, Yandex, Seznam, Naver)';

    public function handle(IndexNowService $service): int
    {
        if ($url = $this->option('url')) {
            $result = $service->submitSingle($url);
            $this->info('Submitted 1 URL. Success: ' . ($result['success'] ? 'yes' : 'no'));

            return 0;
        }

        $urls = $this->gatherAllPseoUrls();

        if ($this->option('new')) {
            $result = $service->submitNewOnly($urls);
            $this->info("New URLs submitted: {$result['submitted']}");

            return 0;
        }

        if ($this->option('all')) {
            $this->info('Submitting all PSEO URLs to IndexNow...');
            $chunks = array_chunk($urls, 5000);
            $total = 0;

            foreach ($chunks as $chunk) {
                $result = $service->submit($chunk);
                if ($result['success']) {
                    $total += count($chunk);
                }
                if (count($chunks) > 1) {
                    sleep(1);
                }
            }

            $this->info("Successfully submitted {$total} URLs.");
            return 0;
        }

        // Default: --new
        $result = $service->submitNewOnly($urls);
        $this->info("New URLs submitted: {$result['submitted']}");

        return 0;
    }

    protected function gatherAllPseoUrls(): array
    {
        $urls = [];

        $urls[] = url('/');
        $urls[] = url('/features');
        $urls[] = url('/pricing');
        $urls[] = url('/contact');
        $urls[] = url('/docs');
        $urls[] = url('/kb');

        foreach (array_keys(config('pseo.industries', [])) as $slug) {
            $urls[] = url("/best-crm-for-{$slug}");
            $urls[] = url("/best-crm-for-{$slug}-" . now()->year);
        }

        foreach (array_keys(config('pseo.competitors', [])) as $slug) {
            $urls[] = url("/alternatives-to-{$slug}");
        }

        foreach (array_keys(config('pseo.countries', [])) as $slug) {
            $urls[] = url("/crm-for-{$slug}");
        }

        foreach (array_keys(config('pseo.features', [])) as $slug) {
            $urls[] = url("/crm-with-{$slug}");
        }

        foreach (array_keys(config('pseo.roles', [])) as $slug) {
            $urls[] = url("/{$slug}-crm");
        }

        foreach (array_keys(config('pseo.pricing-bands', [])) as $price) {
            $urls[] = url("/best-crm-under-{$price}");
        }

        foreach (array_keys(config('pseo.industries', [])) as $industry) {
            foreach (array_keys(config('pseo.cities', [])) as $city) {
                $urls[] = url("/crm-for-{$industry}-in-{$city}");
            }
        }

        $competitors = array_keys(config('pseo.competitors', []));
        foreach ($competitors as $c) {
            $urls[] = url("/compare/crmoffice-vs-{$c}");
        }

        KbCategory::where('is_public', true)->get()->each(function ($cat) use (&$urls) {
            $urls[] = url("/kb/{$cat->slug}");
        });

        KbArticle::where('is_published', true)->with('category')->get()->each(function ($art) use (&$urls) {
            if ($art->category && $art->category->is_public) {
                $urls[] = url("/kb/{$art->category->slug}/{$art->slug}");
            }
        });

        BlogPost::where('is_published', true)
            ->where('published_at', '<=', now())
            ->get()
            ->each(function ($post) use (&$urls) {
                $urls[] = url("/blog/{$post->slug}");
            });

        return array_unique($urls);
    }
}
