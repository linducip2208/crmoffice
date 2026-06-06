<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [];
        $allOk = true;

        $checks['mysql'] = $this->probe(fn () => DB::connection()->select('SELECT 1'));
        $allOk = $allOk && $checks['mysql']['ok'];

        $checks['cache'] = $this->probe(function () {
            cache()->set('healthz_probe', '1', 5);
            if (cache()->get('healthz_probe') !== '1') {
                throw new \RuntimeException('cache round-trip failed');
            }
        });
        $allOk = $allOk && $checks['cache']['ok'];

        $checks['storage'] = $this->probe(function () {
            $disk = Storage::disk(config('filesystems.default'));
            $disk->put('healthz_probe.txt', 'ok');
            if ($disk->get('healthz_probe.txt') !== 'ok') {
                throw new \RuntimeException('storage round-trip failed');
            }
            $disk->delete('healthz_probe.txt');
        });
        $allOk = $allOk && $checks['storage']['ok'];

        if (config('scout.driver') === 'meilisearch' && config('scout.meilisearch.host')) {
            $checks['meilisearch'] = $this->probe(function () {
                $res = Http::timeout(2)->get(rtrim(config('scout.meilisearch.host'), '/').'/health');
                if (! $res->successful()) {
                    throw new \RuntimeException('meilisearch health endpoint not ok');
                }
            });
            $allOk = $allOk && $checks['meilisearch']['ok'];
        }

        $checks['queue'] = ['ok' => true, 'driver' => config('queue.default')];

        return response()->json([
            'ok' => $allOk,
            'app' => config('app.name', 'crmoffice'),
            'version' => '0.1.0',
            'checks' => $checks,
            'time' => now()->toIso8601String(),
        ], $allOk ? 200 : 503);
    }

    private function probe(callable $fn): array
    {
        try {
            $fn();

            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
