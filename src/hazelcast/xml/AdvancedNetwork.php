<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\hazelcast\xml;

use dev\winterframework\paxb\attr\XmlElement;
use dev\winterframework\paxb\attr\XmlRootElement;

#[XmlRootElement("advanced-network")]
class AdvancedNetwork {

    #[XmlElement(name: "memcache-server-socket-endpoint-config")]
    private ?MemcacheServerConfig $memcached = null;

    public function getMemcached(): ?MemcacheServerConfig {
        return $this->memcached;
    }
    
}