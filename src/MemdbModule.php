<?php
/** @noinspection DuplicatedCode */
declare(strict_types=1);

namespace dev\winterframework\memdb;

use Apache\Ignite\ClientConfiguration;
use Apache\Ignite\Exception\ClientException;
use dev\winterframework\core\app\WinterModule;
use dev\winterframework\core\context\ApplicationContext;
use dev\winterframework\core\context\ApplicationContextData;
use dev\winterframework\core\context\WinterBeanProviderContext;
use dev\winterframework\core\context\WinterServer;
use dev\winterframework\data\memcache\mc\MemcacheTemplate;
use dev\winterframework\data\memcache\mc\MemcacheTemplateImpl;
use dev\winterframework\data\memcache\mcd\MemcachedTemplate;
use dev\winterframework\data\memcache\mcd\MemcachedTemplateImpl;
use dev\winterframework\data\memcache\MemcacheModule;
use dev\winterframework\data\redis\phpredis\PhpRedisTemplate;
use dev\winterframework\data\redis\RedisModule;
use dev\winterframework\exception\BeansException;
use dev\winterframework\exception\ModuleException;
use dev\winterframework\io\timer\IdleCheckRegistry;
use dev\winterframework\memdb\exception\MemdbException;
use dev\winterframework\memdb\hazelcast\HazelcastServerProcess;
use dev\winterframework\memdb\ignite\IgniteCacheTemplate;
use dev\winterframework\memdb\ignite\IgniteServerProcess;
use dev\winterframework\memdb\memcached\MemcachedServerProcess;
use dev\winterframework\memdb\redis\RedisServerProcess;
use dev\winterframework\reflection\ReflectionUtil;
use dev\winterframework\stereotype\Module;
use dev\winterframework\util\ConfigFileLoader;
use dev\winterframework\util\log\Wlf4p;
use dev\winterframework\util\ModuleTrait;

#[Module]
class MemdbModule implements WinterModule {
    use Wlf4p;
    use ModuleTrait;

    public function init(ApplicationContext $ctx, ApplicationContextData $ctxData): void {
        ReflectionUtil::assertPhpExtension('swoole');
    }

    public function begin(ApplicationContext $ctx, ApplicationContextData $ctxData): void {
        $moduleDef = $ctx->getModule(static::class);
        $config = $this->retrieveConfiguration($ctx, $ctxData, $moduleDef);

        if (isset($config['redis']) && is_array($config['redis'])) {
            $this->buildRedisServers($config, $ctx, $ctxData);
        }

        if (isset($config['memcached']) && is_array($config['memcached'])) {
            $this->buildMemcachedServers($config, $ctx, $ctxData);
        }

        if (isset($config['ignite']) && is_array($config['ignite'])) {
            $this->buildIgniteServers($config, $ctx, $ctxData);
        }

        if (isset($config['hazelcast']) && is_array($config['hazelcast'])) {
            $this->buildHazelcastServers($config, $ctx, $ctxData);
        }
    }

    protected function buildRedisServers(
        array $config,
        ApplicationContext $ctx,
        ApplicationContextData $ctxData
    ): void {

        if (!class_exists(RedisModule::class, true)) {
            throw new ModuleException('"RedisModule" dependency is not available for Memdb module Redis to work');
        }

        /** @var WinterServer $executor */
        $wServer = $ctx->beanByClass(WinterServer::class);
        $servers = $config['redis'];

        /** @var WinterBeanProviderContext $beanProvider */
        $beanProvider = $ctxData->getBeanProvider();
        /** @var IdleCheckRegistry $idleCheck */
        $idleCheck = $ctx->beanByClass(IdleCheckRegistry::class);

        foreach ($servers as $id => $server) {
            $id = $id + 1;
            if (!isset($server['name'])) {
                $server['name'] = 'memdb-redis-' . $id;
            }
            if (isset($server['confFile'])) {
                $server['confFile'] = ConfigFileLoader::retrieveConfigurationFile($ctxData, $server['confFile']);
            }
            $ps = new RedisServerProcess($wServer, $ctx, $id, $server);
            $wServer->addProcess($ps);

            if ($ctx->hasBeanByName($server['name'])) {
                throw new BeansException("Bean already exist with name '" . $server['name']
                    . "' Memdb Redis bean name conflicts with other bean");
            }

            $address = preg_split('/\s+/', $ps->getAddress(), 2);

            $dataConfig = [
                'name' => $server['name'],
                'idleTimeout' => 300,
                'timeout' => 2,
                'host' => $address[0] ?: '127.0.0.1',
                'port' => $ps->getPort()
            ];

            $tpl = new PhpRedisTemplate($dataConfig, true);
            $beanProvider->registerInternalBean(
                $tpl,
                PhpRedisTemplate::class,
                false,
                $dataConfig['name'],
                true
            );
            $idleCheck->register([$tpl, 'checkIdleConnection']);
        }

        self::logInfo('Loading "Redis" Servers ... Done!');
    }

    protected function buildMemcachedServers(
        array $config,
        ApplicationContext $ctx,
        ApplicationContextData $ctxData
    ): void {

        if (!class_exists(MemcacheModule::class, true)) {
            throw new ModuleException('"MemcacheModule" dependency is not available for Memdb module memcached to work');
        }

        /** @var WinterServer $executor */
        $wServer = $ctx->beanByClass(WinterServer::class);
        $servers = $config['memcached'];

        /** @var WinterBeanProviderContext $beanProvider */
        $beanProvider = $ctxData->getBeanProvider();
        /** @var IdleCheckRegistry $idleCheck */
        $idleCheck = $ctx->beanByClass(IdleCheckRegistry::class);

        foreach ($servers as $id => $server) {
            $id = $id + 1;
            if (!isset($server['name'])) {
                $server['name'] = 'memdb-memcached-' . $id;
            }
            $ps = new MemcachedServerProcess($wServer, $ctx, $id, $server);
            $wServer->addProcess($ps);

            if ($ctx->hasBeanByName($server['name'])) {
                throw new BeansException("Bean already exist with name '" . $server['name']
                    . "' Memdb memcached bean name conflicts with other bean");
            }

            $hosts = [];
            $address = $ps->getAddress();
            $port = $ps->getPort();
            if ($ps->getPortType() == MemcachedServerProcess::PORT_TYPE_UNX_SOCK) {
                $address = $ps->getPort();
                $port = 0;
            }
            $hosts[] = [
                'host' => $address,
                'port' => $port,
                'weight' => 0
            ];

            $dataConfig = [
                'name' => $server['name'],
                'idleTimeout' => 300,
                'timeout' => 2,
                'servers' => $hosts
            ];

            $klassImpl = ReflectionUtil::phpExtension('memcached') ? MemcachedTemplateImpl::class : MemcacheTemplateImpl::class;
            $klass = ReflectionUtil::phpExtension('memcached') ? MemcachedTemplate::class : MemcacheTemplate::class;

            $tpl = new $klassImpl($dataConfig, true);
            $beanProvider->registerInternalBean(
                $tpl,
                $klass,
                false,
                $dataConfig['name'],
                true
            );
            $idleCheck->register([$tpl, 'checkIdleConnection']);
        }

        self::logInfo('Loading "Memcached" Servers ... Done!');
    }

    protected function buildIgniteServers(
        array $config,
        ApplicationContext $ctx,
        ApplicationContextData $ctxData
    ): void {
        /** @var WinterServer $executor */
        $wServer = $ctx->beanByClass(WinterServer::class);
        $servers = $config['ignite'];

        /** @var WinterBeanProviderContext $beanProvider */
        $beanProvider = $ctxData->getBeanProvider();
        /** @var IdleCheckRegistry $idleCheck */
        $idleCheck = $ctx->beanByClass(IdleCheckRegistry::class);

        foreach ($servers as $id => $server) {
            $id = $id + 1;
            if (!isset($server['name'])) {
                $server['name'] = 'memdb-ignite-' . $id;
            }
            if (isset($server['confFile'])) {
                $server['confFile'] = ConfigFileLoader::retrieveConfigurationFile($ctxData, $server['confFile']);
            }
            $ps = new IgniteServerProcess($wServer, $ctx, $id, $server);
            $wServer->addProcess($ps);

            if ($ctx->hasBeanByName($server['name'])) {
                throw new BeansException("Bean already exist with name '" . $server['name']
                    . "' Memdb ignite bean name conflicts with other bean");
            }

            $startUpTimeMs = $this->getBootUpTime($server, 5000);

            try {
                $ic = new ClientConfiguration($ps->getAddress() . ':' . $ps->getPort());
            } catch (ClientException $e) {
                throw new MemdbException($e->getMessage(), 0, $e);
            }
            if (isset($server['username'])) {
                $ic->setUserName($server['username']);
            }
            if (isset($server['password'])) {
                $ic->setPassword($server['password']);
            }

            $tls = [];
            if (isset($server['tls.local_cert'])) {
                $tls['local_cert'] = $server['tls.local_cert'];
            }
            if (isset($server['tls.cafile'])) {
                $tls['cafile'] = $server['tls.cafile'];
            }
            if (isset($server['tls.local_pk'])) {
                $tls['local_pk'] = $server['tls.local_pk'];
            }

            if ($tls) {
                $ic->setTLSOptions($tls);
            }

            $cacheTpl = new IgniteCacheTemplate($ic, $ctx, $startUpTimeMs);
            $beanProvider->registerInternalBean(
                $cacheTpl,
                IgniteCacheTemplate::class,
                false,
                $server['name'],
                true
            );

            $idleCheck->register([$cacheTpl, 'checkIdleConnection']);
        }

        self::logInfo('Loading "Ignite" Servers ... Done!');
    }

    protected function buildHazelcastServers(
        array $config,
        ApplicationContext $ctx,
        ApplicationContextData $ctxData
    ): void {
        /** @var WinterServer $executor */
        $wServer = $ctx->beanByClass(WinterServer::class);
        $servers = $config['hazelcast'];

        /** @var WinterBeanProviderContext $beanProvider */
        $beanProvider = $ctxData->getBeanProvider();
        /** @var IdleCheckRegistry $idleCheck */
        $idleCheck = $ctx->beanByClass(IdleCheckRegistry::class);

        foreach ($servers as $id => $server) {
            $id = $id + 1;
            if (!isset($server['name'])) {
                $server['name'] = 'memdb-hazelcast-' . $id;
            }
            if (isset($server['confFile'])) {
                $server['confFile'] = ConfigFileLoader::retrieveConfigurationFile($ctxData, $server['confFile']);
            }
            $ps = new HazelcastServerProcess($wServer, $ctx, $id, $server);
            $wServer->addProcess($ps);

            if ($ctx->hasBeanByName($server['name'])) {
                throw new BeansException("Bean already exist with name '" . $server['name']
                    . "' Memdb hazelcast bean name conflicts with other bean");
            }

            $startUpTimeMs = $this->getBootUpTime($server, 5000);

            $hosts = [];
            $address = $ps->getAddress();
            $port = $ps->getPort();
            $hosts[] = [
                'host' => $address,
                'port' => $port,
                'weight' => 0
            ];

            $dataConfig = [
                'name' => $server['name'],
                'idleTimeout' => 300,
                'bootUpTimeMs' => $startUpTimeMs,
                'timeout' => 2,
                'servers' => $hosts
            ];

            $tpl = new MemcachedTemplateImpl($dataConfig, true);
            $beanProvider->registerInternalBean(
                $tpl,
                MemcachedTemplate::class,
                false,
                $dataConfig['name'],
                true
            );
            $beanProvider->beanByName($dataConfig['name']);
            $beanProvider->beanByNameClass($dataConfig['name'], MemcachedTemplate::class);
            self::logInfo("Hazelcast Bean " . $dataConfig['name'] . ' Created!');
            $idleCheck->register([$tpl, 'checkIdleConnection']);
        }

        self::logInfo('Loading "Hazelcast" Servers ... Done!');
    }

    protected function getBootUpTime(mixed $server, int $default): int {
        $startUpTimeMs = $server['bootUpTimeMs'] ?? $default;
        $startUpTimeMs = intval($startUpTimeMs);
        return ($startUpTimeMs <= 0) ? $default : $startUpTimeMs;
    }

}