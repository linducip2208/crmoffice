<?php

namespace App\Adapters\Storage;

use App\Models\Provider;
use Illuminate\Filesystem\FilesystemAdapter;

interface StorageAdapterContract
{
    public function __construct(Provider $provider);

    public function buildDisk(): FilesystemAdapter;

    public function probeConnection(): array;
}
