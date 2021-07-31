<?php
declare(strict_types=1);

namespace dev\winterframework\memdb\ignite\xml;

use dev\winterframework\paxb\attr\XmlRootElement;
use dev\winterframework\paxb\attr\XmlValue;

#[XmlRootElement("value")]
class Value {

    #[XmlValue]
    private string $value;

    public function getValue(): string {
        return $this->value;
    }

}