<?php

declare(strict_types=1);

namespace App\Services\Pricing\Cache;

use Illuminate\Support\Facades\Cache;

/**
 * Usa o sistema de cache do próprio Laravel.
 * Em produção pode ser Redis, Memcached, etc. — basta mudar o driver no .env
 */
class LaravelCacheAdapter implements PriceCacheInterface
{
    private string $prefix = 'price_calc_';

    public function get(string $key): ?array
    {
        $value = Cache::get($this->prefix . $key);

        if ($value === null) {
            return null;
        }

        return $value;
    }

    public function set(string $key, array $data, int $ttlSeconds = 300): void
    {
        Cache::put($this->prefix . $key, $data, $ttlSeconds);
    }

    public function forget(string $key): void
    {
        Cache::forget($this->prefix . $key);
    }

    /**
     * Gera uma chave de cache baseada nos parâmetros do cálculo.
     * Assim cálculos idênticos reutilizam o mesmo resultado.
     */
    public static function buildKey(array $params): string
    {
        ksort($params);
        return md5(serialize($params));
    }
}
