<?php

declare(strict_types=1);

namespace App\Services\Pricing\Cache;

interface PriceCacheInterface
{
    public function get(string $key): ?array;
    public function set(string $key, array $data, int $ttlSeconds = 300): void;
    public function forget(string $key): void;
}
