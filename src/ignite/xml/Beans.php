<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\ignite\xml;

use dev\winterframework\paxb\attr\XmlElement;
use dev\winterframework\paxb\attr\XmlRootElement;

#[XmlRootElement("beans")]
class Beans {

    #[XmlElement(name: "bean", listClass: Bean::class)]
    private array $beans = [];

    /**
     * @return Bean[]
     */
    public function getBeans(): array {
        return $this->beans;
    }
}