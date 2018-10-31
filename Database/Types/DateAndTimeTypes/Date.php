<?php
namespace PHPDbLib\Database\Types\DateAndTimeTypes;

use PHPDbLib\Database\Types\ColumnTypes\NoLengthType;

class Date extends NoLengthType {
    protected $type="DATE";

    function __construct($name)
    {
        parent::__construct($name);
    }
}
