<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\hazelcast\xml;

use dev\winterframework\paxb\attr\XmlElement;
use dev\winterframework\paxb\attr\XmlRootElement;

#[XmlRootElement("network")]
class Network {

    #[XmlElement(name: "port")]
    private ?Port $port = null;

    public function getPort(): ?Port {
        return $this->port;
    }

}