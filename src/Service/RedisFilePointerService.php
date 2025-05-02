<?php

namespace App\Service;

use Predis\Client;

class RedisFilePointerService implements FilePointerManagerInterface
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function getFilePointer(string $filePath): int
    {
        // Retrieve the pointer from Redis, default to 0 if not found
        return (int) $this->redis->get($this->getRedisKey($filePath)) ?: 0;
    }

    public function setFilePointer(string $filePath, int $pointer): void
    {
        // Save the pointer to Redis
        $this->redis->set($this->getRedisKey($filePath), $pointer);
    }

    private function getRedisKey(string $filePath): string
    {
        // Generate a unique key based on the file path to store the pointer
        return "log_pointer:{$filePath}";
    }
}
