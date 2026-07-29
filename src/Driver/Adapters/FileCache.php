<?php

namespace Cloudbadak\PaymentHub\Driver\Adapters;

use Cloudbadak\PaymentHub\Contracts\CacheInterface;

class FileCache implements CacheInterface
{
    protected string $path;
    protected string $extension = '.cache';

    public function __construct(array $config = [])
    {
        $this->path = $config['path'] ?? sys_get_temp_dir() . '/payment-hub-cache';
        
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }

        if (isset($config['extension'])) {
            $this->extension = $config['extension'];
        }
    }

    public function get(string $key, $default = null)
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return $default;
        }

        $content = unserialize(file_get_contents($file));

        if ($content['expires'] !== null && $content['expires'] < time()) {
            $this->remove($key);
            return $default;
        }

        return $content['data'];
    }

    public function save(string $key, $data, ?int $ttl = null): bool
    {
        try {
            $file = $this->getFilePath($key);
            
            $content = [
                'data' => $data,
                'expires' => $ttl !== null ? time() + $ttl : null,
            ];

            $dir = dirname($file);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($file, serialize($content));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function remove(string $key): bool
    {
        try {
            $file = $this->getFilePath($key);
            if (file_exists($file)) {
                unlink($file);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function flush(): bool
    {
        try {
            $files = glob($this->path . '/*' . $this->extension);
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getFilePath(string $key): string
    {
        $hash = hash('sha256', $key);
        $subdir = substr($hash, 0, 2);
        return $this->path . '/' . $subdir . '/' . $hash . $this->extension;
    }
}
