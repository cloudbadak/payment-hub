<?php

namespace Cloudbadak\PaymentHub\Driver\Adapters;

use Cloudbadak\PaymentHub\Contracts\CacheInterface;
use CodeIgniter\Cache\CacheInterface as CodeIgniterCache;

class CodeIgniterCacheAdapter implements CacheInterface
{
    protected CodeIgniterCache $driver;

    public function __construct(CodeIgniterCache $driver)
    {
        $this->driver = $driver;
    }

    public function get(string $key, $default = null)
    {
        $value = $this->driver->get($key);
        return $value !== null ? $value : $default;
    }

    public function save(string $key, $data, ?int $ttl = null): bool
    {
        try {
            $this->driver->save($key, $data, $ttl ?? 60);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function remove(string $key): bool
    {
        try {
            $this->driver->delete($key);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function flush(): bool
    {
        try {
            $this->driver->clean();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
