<?php

namespace Cloudbadak\PaymentHub\Driver\Adapters;

use Cloudbadak\PaymentHub\Contracts\CacheInterface;

class MemcacheAdapter implements CacheInterface
{
    protected \Memcache $client;

    public function __construct(array $config = [])
    {
        $this->client = new \Memcache();
        
        $servers = $config['servers'] ?? [
            ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 1]
        ];

        try {
            foreach ($servers as $server) {
                $host = $server['host'] ?? '127.0.0.1';
                $port = $server['port'] ?? 11211;
                $weight = $server['weight'] ?? 1;
                
                $this->client->addServer($host, $port, true, $weight);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException("Memcache connection failed: " . $e->getMessage());
        }
    }

    public function get(string $key, $default = null)
    {
        try {
            $value = $this->client->get($key);
            return $value !== false ? unserialize($value) : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    public function save(string $key, $data, ?int $ttl = null): bool
    {
        try {
            $serialized = serialize($data);
            $expire = $ttl !== null ? time() + $ttl : 0;
            
            $this->client->set($key, $serialized, MEMCACHE_COMPRESSED, $expire);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function remove(string $key): bool
    {
        try {
            $this->client->delete($key);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function flush(): bool
    {
        try {
            $this->client->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function __destruct()
    {
        try {
            $this->client->close();
        } catch (\Exception $e) {
            // Ignore
        }
    }
}
