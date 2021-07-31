<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\hazelcast\config;

use dev\winterframework\io\file\BasicFile;
use dev\winterframework\memdb\hazelcast\xml\Hazelcast;
use dev\winterframework\paxb\XmlObjectMapper;
use dev\winterframework\util\yaml\YamlParser;

class HazelcastConfigBuilder {

    public function build(string $file): HazelcastConfig {
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        $c = new HazelcastConfig();

        if ($ext === 'yml' || $ext === 'yaml') {
            $yml = YamlParser::parseFile($file);
            $c->setPort($yml['hazelcast.network.port.port'] ?? 5701);
            $c->setPortIncrement($yml['hazelcast.network.port.auto-increment'] ?? false);
            $c->setPortCount($yml['hazelcast.network.port.port-count'] ?? 0);
        } else {
            $f = new BasicFile($file);
            $mapper = new XmlObjectMapper();
            /** @var Hazelcast $hz */
            $hz = $mapper->readValueFromFile($f, Hazelcast::class, false);

            $port = null;
            if ($hz->getAdvancedNetwork()
                && $hz->getAdvancedNetwork()->getMemcached()
                && $hz->getAdvancedNetwork()->getMemcached()->getPort()
            ) {
                $port = $hz->getAdvancedNetwork()->getMemcached()->getPort();
            } else if ($hz->getNetwork()
                && $hz->getNetwork()->getPort()
            ) {
                $port = $hz->getNetwork()->getPort();
            }

            if ($port) {
                $c->setPort($port->getPort());
                $c->setPortIncrement($port->isAutoIncrement());
                $c->setPortCount($port->getPortCount());
            }
        }
        $c->setClusterName($hz->getClusterName() ?? '');

        return $c;
    }
}