<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\ignite\config;

use dev\winterframework\io\file\BasicFile;
use dev\winterframework\memdb\ignite\xml\Beans;
use dev\winterframework\paxb\XmlObjectMapper;

class IgniteXmlConfigBuilder {

    public function build(string $file): IgniteXmlConfig {
        $mapper = new XmlObjectMapper();

        /** @var Beans $beans */
        $beans = $mapper->readValueFromFile(new BasicFile($file), Beans::class, false);

        $c = new IgniteXmlConfig();

        foreach ($beans->getBeans() as $bean) {
            if ($bean->getClass() != 'org.apache.ignite.configuration.IgniteConfiguration') {
                continue;
            }

            foreach ($bean->getProperties() as $property) {
                if ($property->getName() == 'workDirectory') {
                    $c->setWorkDirectory($property->getValue());
                } else if ($property->getName() == 'authenticationEnabled') {
                    $c->setAuthenticationEnabled($property->getValue() == 'true');
                } else if ($property->getName() == 'clientConnectorConfiguration') {
                    foreach ($property->getBeans() as $propBean) {
                        if ($propBean->getClass() != 'org.apache.ignite.configuration.ClientConnectorConfiguration') {
                            continue;
                        }

                        foreach ($propBean->getProperties() as $pp) {
                            switch ($pp->getName()) {
                                case 'port':
                                    $c->setPort(intval($pp->getValue()));
                                    break;

                                case 'sslEnabled':
                                    $c->setSslEnabled($pp->getValue() == 'true');
                                    break;
                            }
                        }

                        break;
                    }
                }
            }

            break;
        }

        return $c;
    }
}