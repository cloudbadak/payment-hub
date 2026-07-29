<?php

namespace Cloudbadak\PaymentHub\Driver;

use Cloudbadak\PaymentHub\Contracts\CacheInterface;
use Cloudbadak\PaymentHub\Driver\Adapters\FileCache;
use Cloudbadak\PaymentHub\Driver\Adapters\RedisCache;
use Cloudbadak\PaymentHub\Driver\Adapters\MemcacheAdapter;
use Cloudbadak\PaymentHub\Driver\Adapters\DatabaseCache;
use Cloudbadak\PaymentHub\Driver\Adapters\LaravelCacheAdapter;
use Cloudbadak\PaymentHub\Driver\Adapters\CodeIgniterCacheAdapter;

class CacheManager
{
    protected array $config;
    protected array $drivers = [];
    protected string $defaultDriver;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->defaultDriver = $config['default'] ?? 'file';
    }

    /**
     * Get cache driver instance
     *
     * @param string|null $driver Driver name (uses default if null)
     * @return Cache
     */
    public function driver(?string $driver = null): Cache
    {
        $driver = $driver ?? $this->defaultDriver;
        
        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = new Cache($this->createAdapter($driver));
        }

        return $this->drivers[$driver];
    }

    /**
     * Create cache adapter instance
     *
     * @param string $driver
     * @return CacheInterface
     */
    protected function createAdapter(string $driver): CacheInterface
    {
        if (!isset($this->config['drivers'][$driver])) {
            throw new \RuntimeException("Cache driver '{$driver}' is not configured");
        }

        $driverConfig = $this->config['drivers'][$driver];
        $type = $driverConfig['type'] ?? $driver;

        switch ($type) {
            case 'file':
                return new FileCache($driverConfig);

            case 'redis':
                return new RedisCache($driverConfig);

            case 'memcache':
                return new MemcacheAdapter($driverConfig);

            case 'database':
                return new DatabaseCache($driverConfig);

            case 'laravel':
                $store = $driverConfig['store'] ?? null;
                return new LaravelCacheAdapter($store);

            case 'codeigniter':
                if (!isset($driverConfig['instance'])) {
                    throw new \RuntimeException("CodeIgniter cache instance is required");
                }
                return new CodeIgniterCacheAdapter($driverConfig['instance']);

            default:
                throw new \RuntimeException("Unsupported cache driver: {$type}");
        }
    }

    /**
     * Set configuration
     *
     * @param array $config
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * Add driver configuration
     *
     * @param string $name
     * @param string $type
     * @param array $config
     * @return self
     */
    public function addDriver(string $name, string $type, array $config = []): self
    {
        if (!isset($this->config['drivers'])) {
            $this->config['drivers'] = [];
        }

        $this->config['drivers'][$name] = array_merge(['type' => $type], $config);
        
        // Clear cached instance
        unset($this->drivers[$name]);

        return $this;
    }

    /**
     * Get all configured driver names
     *
     * @return array
     */
    public function getDrivers(): array
    {
        return array_keys($this->config['drivers'] ?? []);
    }
}
