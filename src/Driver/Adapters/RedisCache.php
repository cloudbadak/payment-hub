<?php

namespace Cloudbadak\PaymentHub\Driver\Adapters;

use Cloudbadak\PaymentHub\Contracts\CacheInterface;

class RedisCache implements CacheInterface
{
    protected \Redis $client;

    public function __construct(array $config = [])
    {
        $this->client = new \Redis();
        
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 6379;
        $password = $config['password'] ?? null;
        $db = $config['db'] ?? 0;
        $timeout = $config['timeout'] ?? 0;

        try {
            $this->client->connect($host, $port, $timeout);
            
            if ($password !== null) {
                $this->client->auth($password);
            }

            $this->client->select($db);
        } catch (\Exception $e) {
            throw new \RuntimeException("Redis connection failed: " . $e->getMessage());
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
            
            if ($ttl !== null) {
                $this->client->setex($key, $ttl, $serialized);
            } else {
                $this->client->set($key, $serialized);
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function remove(string $key): bool
    {
        try {
            $this->client->del($key);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function flush(): bool
    {
        try {
            $this->client->flushDb();
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
