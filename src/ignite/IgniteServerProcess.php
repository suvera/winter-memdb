<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\ignite;

use dev\winterframework\core\context\ApplicationContext;
use dev\winterframework\core\context\WinterServer;
use dev\winterframework\io\process\MonitoringServerProcess;
use dev\winterframework\io\process\ProcessType;
use dev\winterframework\memdb\exception\MemdbException;

class IgniteServerProcess extends MonitoringServerProcess {

    public function __construct(
        WinterServer $wServer,
        ApplicationContext $ctx,
        protected string|int $workerId,
        protected array $config
    ) {
        parent::__construct($wServer, $ctx);
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
        self::logInfo('Ignite Server started on port ' . $this->address . ':' . $this->port);
    }

    protected function onProcessError(): void {
        throw new MemdbException('Could not span Ignite Service process');
    }

    protected function onProcessDead(): void {
        throw new MemdbException('Could not span Ignite Service process');
    }

    protected function run(): void {
        // TODO: Implement run() method.
    }
}