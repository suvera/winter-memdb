<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\hazelcast\xml;

use dev\winterframework\paxb\attr\XmlAttribute;
use dev\winterframework\paxb\attr\XmlValue;

class Port {
    #[XmlAttribute("auto-increment")]
    private bool $autoIncrement = false;

    #[XmlAttribute("port-count")]
    private int $portCount = 0;

    #[XmlValue]
    private int $port = 5701;

    public function isAutoIncrement(): bool {
        return $this->autoIncrement;
    }

    public function getPortCount(): int {
        return $this->portCount;
    }

    public function getPort(): int {
        return $this->port;
    }

}