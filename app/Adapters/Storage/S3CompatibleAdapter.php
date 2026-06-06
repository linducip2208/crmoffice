<?php

namespace App\Adapters\Storage;

use App\Models\Provider;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

/**
 * Generic S3-compatible storage adapter.
 *
 * Owner inputs in admin: endpoint, region, bucket, access_key, secret_key, use_path_style.
 * Compatible with AWS S3, Cloudflare R2, Wasabi, Backblaze B2, MinIO, DigitalOcean Spaces,
 * Linode, Vultr, IBM Cloud — anything S3-API compatible.
 */
class S3CompatibleAdapter implements StorageAdapterContract
{
    public function __construct(private Provider $provider) {}

    public function buildDisk(): FilesystemAdapter
    {
        $config = $this->provider->extra_config ?? [];

        $client = new \Aws\S3\S3Client([
            'endpoint' => $this->provider->base_url,
            'region' => $config['region'] ?? 'auto',
            'version' => 'latest',
            'use_path_style_endpoint' => (bool) ($config['use_path_style'] ?? true),
            'credentials' => [
                'key' => $config['access_key'] ?? '',
                'secret' => $this->provider->api_key ?? '',
            ],
        ]);

        $adapter = new AwsS3V3Adapter($client, $config['bucket'] ?? 'crmoffice');

        return Storage::createScopedDisk(
            new Filesystem($adapter),
            ['url' => $config['public_url'] ?? null]
        );
    }

    public function probeConnection(): array
    {
        try {
            $config = $this->provider->extra_config ?? [];
            $start = microtime(true);

            $client = new \Aws\S3\S3Client([
                'endpoint' => $this->provider->base_url,
                'region' => $config['region'] ?? 'auto',
                'version' => 'latest',
                'use_path_style_endpoint' => (bool) ($config['use_path_style'] ?? true),
                'credentials' => [
                    'key' => $config['access_key'] ?? '',
                    'secret' => $this->provider->api_key ?? '',
                ],
            ]);

            $client->headBucket(['Bucket' => $config['bucket'] ?? 'crmoffice']);

            return [
                'success' => true,
                'latency_ms' => (int) ((microtime(true) - $start) * 1000),
                'bucket' => $config['bucket'],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
