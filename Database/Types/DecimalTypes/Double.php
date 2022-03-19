<?php
namespace PHPDbLib\Database\Types\DecimalTypes;

use PHPDbLib\Database\Types\ColumnTypes\DecimalType;

class Double extends DecimalType {
    protected $type="DOUBLE";

    function __construct($name,$length,$precision)
    {
        parent::__construct($name,$length,$precision);
    }
}
