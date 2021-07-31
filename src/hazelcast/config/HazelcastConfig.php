<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\hazelcast\config;

class HazelcastConfig {
    private int $port = 5701;
    private int $portCount = 0;
    private bool $portIncrement = false;

    private string $clusterName = '';

    public function getPort(): int {
        return $this->port;
    }

    public function setPort(int $port): void {
        $this->port = $port;
    }

    public function getPortCount(): int {
        return $this->portCount;
    }

    public function setPortCount(int $portCount): void {
        $this->portCount = $portCount;
    }

    public function isPortIncrement(): bool {
        return $this->portIncrement;
    }

    public function setPortIncrement(bool $portIncrement): void {
        $this->portIncrement = $portIncrement;
    }

    public function getClusterName(): string {
        return $this->clusterName;
    }

    public function setClusterName(string $clusterName): void {
        $this->clusterName = $clusterName;
    }

}