<?php
namespace PHPDbLib\Database\Types\DecimalTypes;

use PHPDbLib\Database\Types\ColumnTypes\DecimalType;

class Numeric extends DecimalType {
    protected $type="NUMERIC";

    function __construct($name,$length,$precision)
    {
        parent::__construct($name,$length,$precision);
    }
}
