<?php

namespace App\Http\Middleware;

use App\Services\LicenseClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePair
{
    public function __construct(private LicenseClient $client) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        $domain = strtolower($request->getHost());
        $data   = $this->client->verify($domain);

        if ($data) {
            $request->attributes->set('license', $data);
            return $next($request);
        }

        return redirect()->to('/__pair');
    }

    private function shouldBypass(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');

        // Always allow the wizard itself
        if (str_starts_with($path, '/__pair')) return true;

        // Health check / debug
        if ($path === '/up') return true;
        if (str_starts_with($path, '/_debugbar')) return true;

        // Dev bypass (local + test domains)
        if (config('license.dev_bypass')) {
            $host = $request->getHost();
            if ($this->isDevHost($host)) return true;
        }

        // Only require license for admin/portal/api
        if (str_starts_with($path, '/admin')) return false;
        if (str_starts_with($path, '/portal')) return false;
        if (str_starts_with($path, '/api')) return false;
        if (str_starts_with($path, '/livewire')) return false;
        if (str_starts_with($path, '/webhooks')) return false;
        if (str_starts_with($path, '/two-factor')) return false;

        // Everything else is public — allow
        return true;
    }

    private function isDevHost(string $host): bool
    {
        return $host === 'localhost'
            || $host === '127.0.0.1'
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.localhost');
    }
}
