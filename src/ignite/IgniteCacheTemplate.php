<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\ignite;

use Apache\Ignite\Cache\CacheConfiguration;
use Apache\Ignite\Cache\CacheInterface;
use Apache\Ignite\Client;
use Apache\Ignite\ClientConfiguration;
use Apache\Ignite\Exception\ClientException;
use Co;
use dev\winterframework\core\context\ApplicationContext;
use dev\winterframework\util\log\Wlf4p;

class IgniteCacheTemplate implements IgniteClientOperations {
    use Wlf4p;

    protected ?Client $client = null;

    public function __construct(
        protected ClientConfiguration $configuration,
        protected ApplicationContext $ctx,
        protected int $startUpTimeMs
    ) {
    }

    /**
     * @throws
     */
    protected function initClient(): void {
        if ($this->client) {
            return;
        }

        $this->client = new Client();
        while (1) {
            $ex = null;
            try {
                $this->client->connect($this->configuration);
                break;
            } catch (ClientException $e) {
                $ex = $e;
                $time = intval(microtime(true) * 1000);
                if (($time - $this->ctx->getStartupDate()) > $this->startUpTimeMs) {
                    break;
                }
                Co::sleep(0.05);
            }
        }

        if ($ex) {
            throw $ex;
        }
    }

    /**
     * @throws
     */
    public function createCache(string $name, CacheConfiguration $cacheConfig = null): CacheInterface {
        $this->initClient();
        return $this->client->createCache($name, $cacheConfig);
    }

    /**
     * @throws
     */
    public function getOrCreateCache(string $name, CacheConfiguration $cacheConfig = null): CacheInterface {
        $this->initClient();
        return $this->client->getOrCreateCache($name, $cacheConfig);
    }

    /**
     * @throws
     */
    public function getCache(string $name): CacheInterface {
        $this->initClient();
        return $this->client->getCache($name);
    }

    /**
     * @throws
     */
    public function destroyCache(string $name): void {
        $this->initClient();
        $this->client->destroyCache($name);
    }

    /**
     * @throws
     */
    public function getCacheConfiguration(string $name): CacheConfiguration {
        $this->initClient();
        return $this->client->getCacheConfiguration($name);
    }

    /**
     * @throws
     */
    public function cacheNames(): array {
        $this->initClient();
        return $this->client->cacheNames();
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function close($safe = false): void {
        $this->client->disconnect();
        $this->client = null;
    }

    public function checkIdleConnection(): void {
        // template
    }
}