<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\hazelcast\xml;

use dev\winterframework\paxb\attr\XmlElement;
use dev\winterframework\paxb\attr\XmlRootElement;

#[XmlRootElement("hazelcast")]
class Hazelcast {

    #[XmlElement(name: "cluster-name")]
    private string $clusterName = '';

    #[XmlElement(name: "network")]
    private ?Network $network = null;

    #[XmlElement(name: "advanced-network")]
    private ?AdvancedNetwork $advancedNetwork = null;

    public function getClusterName(): string {
        return $this->clusterName;
    }

    public function getNetwork(): ?Network {
        return $this->network;
    }

    public function getAdvancedNetwork(): ?AdvancedNetwork {
        return $this->advancedNetwork;
    }

}