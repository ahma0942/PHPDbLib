<?php
namespace PHPDbLib\Database\Types\DateAndTimeTypes;

use PHPDbLib\Database\Types\ColumnTypes\NoLengthType;

class Time extends NoLengthType {
    protected $type="TIME";

    function __construct($name)
    {
        parent::__construct($name);
    }
}
