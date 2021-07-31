<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\ignite;

use dev\winterframework\core\context\ApplicationContext;
use dev\winterframework\core\context\WinterServer;
use dev\winterframework\io\process\MonitoringServerProcess;
use dev\winterframework\io\process\ProcessType;
use dev\winterframework\memdb\exception\MemdbException;
use dev\winterframework\memdb\ignite\config\IgniteXmlConfig;
use dev\winterframework\memdb\ignite\config\IgniteXmlConfigBuilder;
use dev\winterframework\type\Arrays;

class IgniteServerProcess extends MonitoringServerProcess {

    private string $address = '127.0.0.1';
    protected IgniteXmlConfig $xmlConfig;

    public function __construct(
        WinterServer $wServer,
        ApplicationContext $ctx,
        protected string|int $workerId,
        protected array $config
    ) {
        parent::__construct($wServer, $ctx);
        Arrays::assertKey($this->config, 'serverBinary', 'invalid Memdb Ignite config');
        Arrays::assertKey($this->config, 'confFile', 'invalid Memdb Ignite config');
        $this->parse();
    }

    protected function parse(): void {
        if (!file_exists($this->config['confFile'])) {
            throw new MemdbException('Could not find Ignite conf file');
        }
        $builder = new IgniteXmlConfigBuilder();
        $this->xmlConfig = $builder->build($this->config['confFile']);
    }

    public function getChildProcessId(): string {
        return 'ignite-server-' . $this->workerId;
    }

    public function getProcessId(): string {
        return 'ignite-monitor-' . $this->workerId;
    }

    public function getChildProcessType(): int {
        return ProcessType::OTHER;
    }

    public function getProcessType(): int {
        return ProcessType::OTHER;
    }

    protected function onProcessStart(): void {
        self::logInfo('Ignite Server started on port '
            . $this->address . ':' . $this->xmlConfig->getPort());
    }

    protected function onProcessError(): void {
        throw new MemdbException('Could not span Ignite Service process');
    }

    protected function onProcessDead(): void {
        throw new MemdbException('Could not span Ignite Service process');
    }

    protected function run(): void {
        $cmd = $this->config['serverBinary'] . ' ' . $this->config['confFile'];
        self::logInfo($cmd);

        $lineArgs = [];
        $this->launchAndMonitor($cmd, $lineArgs);
    }

    public function getAddress(): string {
        return $this->address;
    }

    public function getPort(): int {
        return $this->xmlConfig->getPort();
    }

    public function getXmlConfig(): IgniteXmlConfig {
        return $this->xmlConfig;
    }

}