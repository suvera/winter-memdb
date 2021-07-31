<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\ignite\xml;

use dev\winterframework\paxb\attr\XmlElement;

class Collection {
    #[XmlElement(name: "bean", listClass: Bean::class)]
    private array $beans = [];

    #[XmlElement(name: "property", listClass: Property::class)]
    private array $properties = [];

    #[XmlElement(name: "value", listClass: Value::class)]
    private array $values = [];

    #[XmlElement(name: "entry", listClass: Entry::class)]
    private array $entries = [];

    /**
     * @return Bean[]
     */
    public function getBeans(): array {
        return $this->beans;
    }

    public function getProperties(): array {
        return $this->properties;
    }

    public function getValues(): array {
        return $this->values;
    }

    public function getEntries(): array {
        return $this->entries;
    }

}