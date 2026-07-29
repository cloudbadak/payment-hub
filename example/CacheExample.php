<?php

/**
 * Cache Configuration Examples
 * 
 * Ini adalah contoh bagaimana menggunakan CacheManager dengan berbagai driver
 */

use Cloudbadak\PaymentHub\Driver\CacheManager;

// ===== FILE CACHE (Default) =====
$cacheManager = new CacheManager([
    'default' => 'file',
    'drivers' => [
        'file' => [
            'type' => 'file',
            'path' => '/var/cache/payment-hub',  // Custom path
            'extension' => '.cache'
        ]
    ]
]);

$fileCache = $cacheManager->driver('file');
$fileCache->save('payment_key', ['transaction_id' => 123], 3600); // 1 hour
$data = $fileCache->get('payment_key');


// ===== REDIS CACHE =====
$cacheManager = new CacheManager([
    'default' => 'redis',
    'drivers' => [
        'redis' => [
            'type' => 'redis',
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,  // Set if Redis requires authentication
            'db' => 0,
            'timeout' => 0
        ]
    ]
]);

$redisCache = $cacheManager->driver('redis');
$redisCache->save('payment:transaction:123', $paymentData, 7200);


// ===== MEMCACHE CACHE =====
$cacheManager = new CacheManager([
    'default' => 'memcache',
    'drivers' => [
        'memcache' => [
            'type' => 'memcache',
            'servers' => [
                ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 1],
                // Multiple servers untuk load balancing
                ['host' => '127.0.0.2', 'port' => 11211, 'weight' => 1],
            ]
        ]
    ]
]);

$memcacheCache = $cacheManager->driver('memcache');


// ===== DATABASE CACHE =====
$cacheManager = new CacheManager([
    'default' => 'database',
    'drivers' => [
        'database' => [
            'type' => 'database',
            'dsn' => 'mysql:host=localhost;dbname=payment_hub',
            'username' => 'root',
            'password' => 'password',
            'table' => 'payment_hub_cache'
        ]
    ]
]);

$databaseCache = $cacheManager->driver('database');


// ===== MULTIPLE DRIVERS =====
$cacheManager = new CacheManager([
    'default' => 'redis',
    'drivers' => [
        'file' => [
            'type' => 'file',
            'path' => sys_get_temp_dir() . '/payment-hub'
        ],
        'redis' => [
            'type' => 'redis',
            'host' => '127.0.0.1',
            'port' => 6379
        ],
        'database' => [
            'type' => 'database',
            'dsn' => 'mysql:host=localhost;dbname=payment_hub',
            'username' => 'root',
            'password' => 'password'
        ],
        'memcache' => [
            'type' => 'memcache',
            'servers' => [
                ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 1]
            ]
        ]
    ]
]);

// Switch between drivers
$cache1 = $cacheManager->driver('redis');    // Redis
$cache2 = $cacheManager->driver('file');     // File
$cache3 = $cacheManager->driver('database'); // Database


// ===== DYNAMIC DRIVER ADDITION =====
$cacheManager = new CacheManager([
    'default' => 'file'
]);

// Add drivers on-the-fly
$cacheManager->addDriver('redis_cache', 'redis', [
    'host' => '127.0.0.1',
    'port' => 6379
]);

$cacheManager->addDriver('mysql_cache', 'database', [
    'dsn' => 'mysql:host=localhost;dbname=payment_hub',
    'username' => 'root',
    'password' => 'password'
]);

$redisInstance = $cacheManager->driver('redis_cache');


// ===== LARAVEL INTEGRATION =====
// Di file configuration atau service provider Laravel
$cacheManager = new CacheManager([
    'default' => 'laravel_redis',
    'drivers' => [
        'laravel_redis' => [
            'type' => 'laravel',
            'store' => 'redis'  // Laravel cache store name
        ],
        'laravel_default' => [
            'type' => 'laravel'
            // Menggunakan default Laravel cache
        ]
    ]
]);

$laravelCache = $cacheManager->driver('laravel_redis');


// ===== CODEIGNITER INTEGRATION =====
// Di CodeIgniter Controller atau Model
use Config\Services;

$cacheManager = new CacheManager([
    'default' => 'ci_cache',
    'drivers' => [
        'ci_cache' => [
            'type' => 'codeigniter',
            'instance' => Services::cache()
        ]
    ]
]);

$ciCache = $cacheManager->driver('ci_cache');


// ===== USAGE EXAMPLE =====
class PaymentService
{
    protected CacheManager $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function processPayment($paymentId)
    {
        $cache = $this->cacheManager->driver('redis');
        $cacheKey = "payment:{$paymentId}";

        // Get from cache
        if ($cachedData = $cache->get($cacheKey)) {
            return $cachedData;
        }

        // Process payment
        $paymentData = $this->fetchPaymentData($paymentId);

        // Store in cache for 1 hour
        $cache->save($cacheKey, $paymentData, 3600);

        return $paymentData;
    }

    public function invalidatePaymentCache($paymentId)
    {
        $cache = $this->cacheManager->driver('redis');
        $cache->remove("payment:{$paymentId}");
    }
}
