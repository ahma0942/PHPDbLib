<?php
namespace PHPDbLib\Database\Types\DecimalTypes;

use PHPDbLib\Database\Types\ColumnTypes\DecimalType;

class Decimal extends DecimalType {
    protected $type="DECIMAL";

    function __construct($name,$length,$precision)
    {
        parent::__construct($name,$length,$precision);
    }
}
