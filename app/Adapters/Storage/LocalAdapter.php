<?php

namespace App\Adapters\Storage;

use App\Models\Provider;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

/**
 * Local-disk storage adapter — for self-hosted single-server setups.
 * Owner inputs path + public_url in admin.
 */
class LocalAdapter implements StorageAdapterContract
{
    public function __construct(private Provider $provider) {}

    public function buildDisk(): FilesystemAdapter
    {
        $config = $this->provider->extra_config ?? [];
        $root = $config['root'] ?? storage_path('app/public');

        $disk = Storage::build([
            'driver' => 'local',
            'root' => $root,
            'url' => $config['public_url'] ?? null,
            'visibility' => 'public',
        ]);

        return $disk;
    }

    public function probeConnection(): array
    {
        $config = $this->provider->extra_config ?? [];
        $root = $config['root'] ?? storage_path('app/public');

        return [
            'success' => is_dir($root) && is_writable($root),
            'root' => $root,
            'writable' => is_writable($root),
        ];
    }
}
