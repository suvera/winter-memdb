<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\ignite\xml;

use dev\winterframework\paxb\attr\XmlAttribute;
use dev\winterframework\paxb\attr\XmlRootElement;

#[XmlRootElement("entry")]
class Entry {

    #[XmlAttribute(name: "name")]
    private string $name;

    #[XmlAttribute(name: "value")]
    private ?string $value = null;

    public function getName(): string {
        return $this->name;
    }

    public function getValue(): ?string {
        return $this->value;
    }

}