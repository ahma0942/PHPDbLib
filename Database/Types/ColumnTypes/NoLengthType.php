<?php
namespace PHPDbLib\Database\Types\ColumnTypes;

class NoLengthType extends ColumnType {
    function __construct($name)
    {
        parent::__construct($name, null);
    }
}
