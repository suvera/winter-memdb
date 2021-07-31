<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\ignite\xml;

use dev\winterframework\paxb\attr\XmlAttribute;
use dev\winterframework\paxb\attr\XmlElement;
use dev\winterframework\paxb\attr\XmlRootElement;

#[XmlRootElement("property")]
class Property {

    #[XmlAttribute(name: "name")]
    private string $name;

    #[XmlAttribute(name: "value")]
    private ?string $value = null;

    #[XmlAttribute(name: "ref")]
    private ?string $ref = null;

    #[XmlElement(name: "bean", listClass: Bean::class)]
    private array $beans = [];

    #[XmlElement(name: "list", listClass: Collection::class)]
    private array $list = [];

    #[XmlElement(name: "set", listClass: Collection::class)]
    private array $sets = [];

    #[XmlElement(name: "map", listClass: Collection::class)]
    private array $map = [];

    public function getName(): string {
        return $this->name;
    }

    public function getValue(): ?string {
        return $this->value;
    }

    /**
     * @return Bean[]
     */
    public function getBeans(): array {
        return $this->beans;
    }

    public function getRef(): ?string {
        return $this->ref;
    }

    /**
     * @return Collection[]
     */
    public function getList(): array {
        return $this->list;
    }

    /**
     * @return Collection[]
     */
    public function getSets(): array {
        return $this->sets;
    }

    /**
     * @return Collection[]
     */
    public function getMap(): array {
        return $this->map;
    }

}