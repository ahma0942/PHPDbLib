<?php
namespace PHPDbLib\Database\Types\DateAndTimeTypes;

use PHPDbLib\Database\Types\ColumnTypes\NoLengthType;

class Timestamp extends NoLengthType {
    protected $type="TIMESTAMP";

    function __construct($name)
    {
        parent::__construct($name);
    }
}
