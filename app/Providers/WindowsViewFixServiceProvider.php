<?php

namespace App\Providers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class WindowsViewFixServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return;
        }

        $this->app->singleton('files', function ($app) {
            return new class extends Filesystem
            {
                public function replace($path, $content, $mode = null): void
                {
                    clearstatcache(true, $path);

                    if (file_exists($path)) {
                        @unlink($path);
                    }

                    file_put_contents($path, $content, LOCK_EX);

                    if ($mode !== null) {
                        @chmod($path, $mode);
                    }
                }
            };
        });
    }
}
