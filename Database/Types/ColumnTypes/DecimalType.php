<?php
namespace PHPDbLib\Database\Types\ColumnTypes;

class DecimalType extends NumberType {
    protected $precision;

    function __construct($name, $length, $precision)
    {
        parent::__construct($name,$length);
        $this->precision=$precision;
    }
}
