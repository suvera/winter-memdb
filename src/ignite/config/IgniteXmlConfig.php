<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\ignite\config;

class IgniteXmlConfig {
    private string $workDirectory = '';
    private int $port = 10800;
    private array $portRange = [];
    private bool $sslEnabled = false;

    private bool $authenticationEnabled = false;

    private array $caches = [];

    public function getWorkDirectory(): string {
        return $this->workDirectory;
    }

    public function setWorkDirectory(string $workDirectory): void {
        $this->workDirectory = $workDirectory;
    }

    public function getPort(): int {
        return $this->port;
    }

    public function setPort(int $port): void {
        $this->port = $port;
    }

    public function getPortRange(): array {
        return $this->portRange;
    }

    public function setPortRange(array $portRange): void {
        $this->portRange = $portRange;
    }

    public function isSslEnabled(): bool {
        return $this->sslEnabled;
    }

    public function setSslEnabled(bool $sslEnabled): void {
        $this->sslEnabled = $sslEnabled;
    }

    public function isAuthenticationEnabled(): bool {
        return $this->authenticationEnabled;
    }

    public function setAuthenticationEnabled(bool $authenticationEnabled): void {
        $this->authenticationEnabled = $authenticationEnabled;
    }

    public function getCaches(): array {
        return $this->caches;
    }

    public function setCaches(array $caches): void {
        $this->caches = $caches;
    }

    public function addCache(string $cacheName): void {
        $this->caches[] = $cacheName;
    }
}