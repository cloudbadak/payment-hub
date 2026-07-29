<?php

namespace Cloudbadak\PaymentHub\Driver\Adapters;

use Cloudbadak\PaymentHub\Contracts\CacheInterface;
use PDO;

class DatabaseCache implements CacheInterface
{
    protected PDO $pdo;
    protected string $table;

    public function __construct(array $config = [])
    {
        $dsn = $config['dsn'] ?? null;
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;
        $this->table = $config['table'] ?? 'payment_hub_cache';

        if (!$dsn) {
            throw new \RuntimeException("Database DSN is required for DatabaseCache");
        }

        try {
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->initializeTable();
        } catch (\Exception $e) {
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    public function get(string $key, $default = null)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT data, expires_at FROM {$this->table} WHERE cache_key = ?");
            $stmt->execute([$key]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return $default;
            }

            if ($row['expires_at'] !== null && $row['expires_at'] < time()) {
                $this->remove($key);
                return $default;
            }

            return unserialize($row['data']);
        } catch (\Exception $e) {
            return $default;
        }
    }

    public function save(string $key, $data, ?int $ttl = null): bool
    {
        try {
            $serialized = serialize($data);
            $expiresAt = $ttl !== null ? time() + $ttl : null;

            // Delete existing key first
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE cache_key = ?");
            $stmt->execute([$key]);

            // Insert new record
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$this->table} (cache_key, data, expires_at, created_at) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$key, $serialized, $expiresAt, time()]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function remove(string $key): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE cache_key = ?");
            $stmt->execute([$key]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function flush(): bool
    {
        try {
            $this->pdo->query("TRUNCATE TABLE {$this->table}");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function initializeTable(): void
    {
        try {
            // Check if table exists
            $stmt = $this->pdo->query("SELECT 1 FROM {$this->table} LIMIT 1");
        } catch (\Exception $e) {
            // Table doesn't exist, create it
            $this->pdo->query(
                "CREATE TABLE IF NOT EXISTS {$this->table} (
                    id INTEGER PRIMARY KEY AUTO_INCREMENT,
                    cache_key VARCHAR(255) NOT NULL UNIQUE,
                    data LONGBLOB NOT NULL,
                    expires_at INT NULL,
                    created_at INT NOT NULL,
                    INDEX idx_expires (expires_at)
                )"
            );
        }
    }
}
