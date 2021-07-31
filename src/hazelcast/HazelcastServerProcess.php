<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\hazelcast;

use dev\winterframework\core\context\ApplicationContext;
use dev\winterframework\core\context\WinterServer;
use dev\winterframework\io\process\MonitoringServerProcess;
use dev\winterframework\io\process\ProcessType;
use dev\winterframework\memdb\exception\MemdbException;
use dev\winterframework\memdb\hazelcast\config\HazelcastConfig;
use dev\winterframework\memdb\hazelcast\config\HazelcastConfigBuilder;
use dev\winterframework\type\Arrays;

class HazelcastServerProcess extends MonitoringServerProcess {

    private string $address = '127.0.0.1';
    protected HazelcastConfig $xmlConfig;

    public function __construct(
        WinterServer $wServer,
        ApplicationContext $ctx,
        protected string|int $workerId,
        protected array $config
    ) {
        parent::__construct($wServer, $ctx);
        Arrays::assertKey($this->config, 'serverBinary', 'invalid Memdb Hazelcast config');
        $this->parse();
    }

    protected function parse(): void {
        if (isset($this->config['confFile'])) {
            if (file_exists($this->config['confFile'])) {
                $builder = new HazelcastConfigBuilder();
                $this->xmlConfig = $builder->build($this->config['confFile']);
            } else {
                throw new MemdbException('Could not find Hazelcast conf file');
            }
        } else {
            $this->xmlConfig = new HazelcastConfig();
            if (isset($this->config['port'])) {
                $this->xmlConfig->setPort(intval($this->config['port']));
            }
        }
    }

    public function getChildProcessId(): string {
        return 'hazelcast-server-' . $this->workerId;
    }

    public function getProcessId(): string {
        return 'hazelcast-monitor-' . $this->workerId;
    }

    public function getChildProcessType(): int {
        return ProcessType::OTHER;
    }

    public function getProcessType(): int {
        return ProcessType::OTHER;
    }

    protected function onProcessStart(): void {
        self::logInfo('Hazelcast Server started on port '
            . $this->address . ':' . $this->xmlConfig->getPort());
    }

    protected function onProcessError(): void {
        throw new MemdbException('Could not span Hazelcast Service process');
    }

    protected function onProcessDead(): void {
        throw new MemdbException('Could not span Hazelcast Service process');
    }

    /** @noinspection DuplicatedCode */
    protected function run(): void {
        $cmd = '';
        if (isset($this->config['confFile'])) {
            $cmd .= 'export JAVA_OPTS="$JAVA_OPTS -Dhazelcast.config=' . $this->config['confFile'] . '" && ';
        }
        $cmd .= $this->config['serverBinary'];
        $args = $data['args'] ?? '';
        $cmd .= ' ' . $args;

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

    public function getXmlConfig(): HazelcastConfig {
        return $this->xmlConfig;
    }

}