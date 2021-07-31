<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\memcached;

use dev\winterframework\core\context\ApplicationContext;
use dev\winterframework\core\context\WinterServer;
use dev\winterframework\io\process\MonitoringServerProcess;
use dev\winterframework\io\process\ProcessType;
use dev\winterframework\memdb\exception\MemdbException;
use dev\winterframework\type\Arrays;

class MemcachedServerProcess extends MonitoringServerProcess {
    const PORT_TYPE_TCP = 1;
    const PORT_TYPE_UDP = 2;
    const PORT_TYPE_UNX_SOCK = 3;

    protected string $address;
    protected string $port;
    protected string $pidFile;
    protected int $portType;

    public function __construct(
        WinterServer $wServer,
        ApplicationContext $ctx,
        protected string|int $workerId,
        protected array $config
    ) {
        parent::__construct($wServer, $ctx);
        Arrays::assertKey($this->config, 'serverBinary', 'invalid Memdb memcached config');
        Arrays::assertKey($this->config, 'host', 'invalid Memdb memcached config');
        Arrays::assertKey($this->config, 'pidfile', 'invalid Memdb memcached config');

        $this->pidFile = $this->config['pidfile'];
        $this->address = $this->config['host'];

        if (isset($this->config['port']) && $this->config['port'] > 0) {
            $this->portType = self::PORT_TYPE_TCP;
            $this->port = '' . $this->config['port'];
        } else if (isset($this->config['udpPort']) && $this->config['udpPort'] > 0) {
            $this->portType = self::PORT_TYPE_UDP;
            $this->port = '' . $this->config['udpPort'];
        } else if (isset($this->config['unixSocket']) && $this->config['unixSocket']) {
            $this->portType = self::PORT_TYPE_UNX_SOCK;
            $this->port = '' . $this->config['unixSocket'];
        } else {
            throw new MemdbException('Neither "port" nor "udpPort" nor "unixSocket" configured for Memcached');
        }
    }

    public function getChildProcessId(): string {
        return 'memcached-server-' . $this->workerId;
    }

    public function getProcessId(): string {
        return 'memcached-monitor-' . $this->workerId;
    }

    public function getChildProcessType(): int {
        return ProcessType::OTHER;
    }

    public function getProcessType(): int {
        return ProcessType::OTHER;
    }

    protected function onProcessStart(): void {
        self::logInfo('Memcached Server started on port ' . $this->address . ':' . $this->port);
    }

    protected function onProcessError(): void {
        throw new MemdbException('Could not span Memcached Service process');
    }

    protected function onProcessDead(): void {
        throw new MemdbException('Memcached Service is down');
    }

    protected function run(): void {
        $cmd = $this->config['serverBinary'] . ' ';
        $cmd .= ' --listen=' . $this->address;

        $cmd .= match ($this->portType) {
            self::PORT_TYPE_UNX_SOCK => ' --unix-socket=' . $this->port,
            self::PORT_TYPE_UDP => ' --udp-port=' . $this->port,
            default => ' --port=' . $this->port,
        };

        $cmd .= ' --pidfile=' . $this->pidFile;

        if (isset($this->config['args'])) {
            $cmd .= ' ' . $this->config['args'];
        }

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

    public function getPortType(): int {
        return $this->portType;
    }

}