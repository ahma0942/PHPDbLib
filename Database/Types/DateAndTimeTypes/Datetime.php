<?php
namespace PHPDbLib\Database\Types\DateAndTimeTypes;

use PHPDbLib\Database\Types\ColumnTypes\NoLengthType;

class Datetime extends NoLengthType {
    protected $type="DATETIME";

    function __construct($name)
    {
        parent::__construct($name);
    }
}
