<?php

namespace Cloudbadak\PaymentHub\Contracts;

interface CacheInterface
{
    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Store an item in the cache.
     *
     * @param string $key
     * @param mixed $data
     * @param int|null $ttl Time to live in seconds (null for indefinite)
     * @return bool
     */
    public function save(string $key, $data, ?int $ttl = null): bool;

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * @return bool
     */
    public function remove(string $key): bool;

    /**
     * Clear all items from the cache.
     *
     * @return bool
     */
    public function flush(): bool;
}
