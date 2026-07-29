<?php

namespace Cloudbadak\PaymentHub\Driver;

use Cloudbadak\PaymentHub\Contracts\CacheInterface;

class Cache
{
    protected CacheInterface $driver;

    public function __construct(CacheInterface $driver)
    {
        $this->driver = $driver;
    }

    public function get(string $key, $default = null)
    {
        return $this->driver->get($key, $default);
    }

    public function save(string $key, $data, ?int $ttl = null): bool
    {
        return $this->driver->save($key, $data, $ttl);
    }

    public function remove(string $key): bool
    {
        return $this->driver->remove($key);
    }

    public function flush(): bool
    {
        return $this->driver->flush();
    }
}