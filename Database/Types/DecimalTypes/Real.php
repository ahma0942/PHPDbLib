<?php
namespace PHPDbLib\Database\Types\DecimalTypes;

use PHPDbLib\Database\Types\ColumnTypes\DecimalType;

class Real extends DecimalType {
    protected $type="REAL";

    function __construct($name,$length,$precision)
    {
        parent::__construct($name,$length,$precision);
    }
}
