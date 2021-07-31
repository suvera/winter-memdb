<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\ignite;

use Apache\Ignite\Cache\CacheConfiguration;
use Apache\Ignite\Cache\CacheInterface;

interface IgniteClientOperations {

    public function createCache(
        string $name,
        CacheConfiguration $cacheConfig = null
    ): CacheInterface;

    public function getOrCreateCache(
        string $name,
        CacheConfiguration $cacheConfig = null
    ): CacheInterface;

    public function getCache(string $name): CacheInterface;

    public function destroyCache(string $name): void;

    public function getCacheConfiguration(string $name): CacheConfiguration;

    public function cacheNames(): array;
}