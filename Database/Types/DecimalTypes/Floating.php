<?php
namespace PHPDbLib\Database\Types\DecimalTypes;

use PHPDbLib\Database\Types\ColumnTypes\DecimalType;

class Floating extends DecimalType {
    protected $type="FLOAT";

    function __construct($name,$length,$precision)
    {
        parent::__construct($name,$length,$precision);
    }
}
