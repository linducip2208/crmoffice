<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    protected string $key;
    protected string $keyLocation;
    protected array $searchEngines = [
        'https://www.bing.com/indexnow',
        'https://yandex.com/indexnow',
        'https://search.seznam.cz/indexnow',
        'https://indexnow.naver.com/indexnow',
    ];

    public function __construct()
    {
        $this->key = trim((string) @file_get_contents(public_path('indexnow-key.txt')));
        $this->keyLocation = config('app.url') . '/indexnow-key.txt';
    }

    public function submit(array $urls): array
    {
        if (empty($urls)) {
            return ['success' => false, 'message' => 'No URLs provided'];
        }

        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url');
        $payload = [
            'host' => $host,
            'key' => $this->key,
            'keyLocation' => $this->keyLocation,
            'urlList' => array_values($urls),
        ];

        $results = [];
        foreach ($this->searchEngines as $engine) {
            try {
                $response = Http::timeout(15)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($engine, $payload);

                $results[$engine] = [
                    'status' => $response->status(),
                    'success' => $response->successful(),
                ];
            } catch (\Throwable $e) {
                $results[$engine] = ['status' => 0, 'error' => $e->getMessage()];
            }
        }

        $successCount = count(array_filter($results, fn ($r) => ($r['success'] ?? false)));
        Log::info('IndexNow: submitted ' . count($urls) . ' URLs — ' . $successCount . '/' . count($this->searchEngines) . ' engines', $results);

        return [
            'success' => $successCount > 0,
            'submitted' => count($urls),
            'engines' => $results,
        ];
    }

    public function submitSingle(string $url): array
    {
        return $this->submit([$url]);
    }

    public function submitNewOnly(array $urls): array
    {
        $previouslySubmitted = cache('indexnow_submitted_urls', []);

        $newUrls = array_values(array_diff($urls, $previouslySubmitted));

        if (empty($newUrls)) {
            return ['success' => true, 'submitted' => 0, 'message' => 'No new URLs'];
        }

        $result = $this->submit($newUrls);

        $allSubmitted = array_unique(array_merge($previouslySubmitted, $newUrls));
        if (count($allSubmitted) > 50000) {
            $allSubmitted = array_slice($allSubmitted, -50000);
        }
        cache(['indexnow_submitted_urls' => $allSubmitted], now()->addYear());

        return $result;
    }
}
