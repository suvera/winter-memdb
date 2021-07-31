<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\ignite\xml;

use dev\winterframework\paxb\attr\XmlAttribute;
use dev\winterframework\paxb\attr\XmlElement;
use dev\winterframework\paxb\attr\XmlRootElement;

#[XmlRootElement("bean")]
class Bean {

    #[XmlAttribute(name: "id")]
    private ?string $id = null;

    #[XmlAttribute(name: "class")]
    private ?string $class = null;

    #[XmlElement(name: "property", listClass: Property::class)]
    private array $properties = [];

    public function getId(): ?string {
        return $this->id;
    }

    public function getClass(): ?string {
        return $this->class;
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array {
        return $this->properties;
    }
}