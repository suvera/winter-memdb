<?php
declare(strict_types=1);

namespace dev\winterframework\memdb;

use dev\winterframework\core\app\WinterModule;
use dev\winterframework\core\context\ApplicationContext;
use dev\winterframework\core\context\ApplicationContextData;
use dev\winterframework\core\context\WinterBeanProviderContext;
use dev\winterframework\core\context\WinterServer;
use dev\winterframework\data\redis\phpredis\PhpRedisTemplate;
use dev\winterframework\data\redis\RedisModule;
use dev\winterframework\exception\BeansException;
use dev\winterframework\exception\ModuleException;
use dev\winterframework\io\timer\IdleCheckRegistry;
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
            $server['confFile'] = ConfigFileLoader::retrieveConfigurationFile($ctxData, $server['confFile']);
            $ps = new RedisServerProcess($wServer, $ctx, $id, $server);
            $wServer->addProcess($ps);

            if ($ctx->hasBeanByName($server['name'])) {
                throw new BeansException("Bean already exist with name '" . $server['name']
                    . "' Memdb Redis bean name conflicts with other bean");
            }

            $address = preg_split('/\s+/', $ps->getAddress(), 2);

            $dataConfig = [
                'name' => $server['name'],
                'idleTimeout' => 20,
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

        self::logInfo('Loading "MemdbModule" ... Done!');
    }

}