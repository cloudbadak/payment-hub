<?php

namespace Cloudbadak\PaymentHub\Driver\Adapters;

use Cloudbadak\PaymentHub\Contracts\CacheInterface;
use Illuminate\Support\Facades\Cache as LaravelCache;

class LaravelCacheAdapter implements CacheInterface
{
    protected $store;

    public function __construct($store = null)
    {
        $this->store = $store;
    }

    public function get(string $key, $default = null)
    {
        if ($this->store) {
            return LaravelCache::store($this->store)->get($key, $default);
        }
        return LaravelCache::get($key, $default);
    }

    public function save(string $key, $data, ?int $ttl = null): bool
    {
        try {
            if ($this->store) {
                LaravelCache::store($this->store)->put($key, $data, $ttl);
            } else {
                LaravelCache::put($key, $data, $ttl);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function remove(string $key): bool
    {
        try {
            if ($this->store) {
                LaravelCache::store($this->store)->forget($key);
            } else {
                LaravelCache::forget($key);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function flush(): bool
    {
        try {
            if ($this->store) {
                LaravelCache::store($this->store)->flush();
            } else {
                LaravelCache::flush();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
