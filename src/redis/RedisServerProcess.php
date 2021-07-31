<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\redis;

use dev\winterframework\core\context\ApplicationContext;
use dev\winterframework\core\context\WinterServer;
use dev\winterframework\io\process\MonitoringServerProcess;
use dev\winterframework\io\process\ProcessType;
use dev\winterframework\memdb\exception\MemdbException;
use dev\winterframework\type\Arrays;
use dev\winterframework\util\ConfigFileLoader;

class RedisServerProcess extends MonitoringServerProcess {

    protected string $address;
    protected string $port;
    protected string $pidFile;

    public function __construct(
        WinterServer         $wServer,
        ApplicationContext   $ctx,
        protected string|int $workerId,
        protected array      $config
    ) {
        parent::__construct($wServer, $ctx);
        Arrays::assertKey($this->config, 'serverBinary', 'invalid Memdb redis config');
        Arrays::assertKey($this->config, 'confFile', 'invalid Memdb redis config');
        $this->parse();
    }

    protected function parse(): void {
        if (!file_exists($this->config['confFile'])) {
            throw new MemdbException('Could not find Redis conf file');
        }

        $data = ConfigFileLoader::parseConfStyled($this->config['confFile']);
        $this->address = $data['bind'] ?? '';
        $this->port = $data['port'] ?? '';
        $this->pidFile = $data['pidfile'] ?? '';

        if (!$this->pidFile) {
            throw new MemdbException('Could not find "pidFile" in Redis conf file');
        }

        if (!$this->port) {
            throw new MemdbException('Could not find "port" in Redis conf file');
        }
    }

    public function getChildProcessId(): string {
        return 'redis-server-' . $this->workerId;
    }

    public function getProcessId(): string {
        return 'redis-monitor-' . $this->workerId;
    }

    public function getChildProcessType(): int {
        return ProcessType::OTHER;
    }

    public function getProcessType(): int {
        return ProcessType::OTHER;
    }

    protected function onProcessStart(): void {
        self::logInfo('Redis Server started on port ' . $this->address . ':' . $this->port);
    }

    protected function onProcessError(): void {
        throw new MemdbException('Could not span Redis Service process');
    }

    protected function onProcessDead(): void {
        throw new MemdbException('Redis Service is down');
    }

    /** @noinspection DuplicatedCode */
    protected function run(): void {
        $cmd = $this->config['serverBinary'] . ' ' . $this->config['confFile'];
        $args = $data['args'] ?? '';
        $cmd .= ' ' . $args;

        self::logInfo($cmd);

        $lineArgs = [];
        $this->launchAndMonitor($cmd, $lineArgs);
    }

    public function getAddress(): string {
        return $this->address;
    }

    public function getPort(): string {
        return $this->port;
    }

    public function getPidFile(): string {
        return $this->pidFile;
    }
}