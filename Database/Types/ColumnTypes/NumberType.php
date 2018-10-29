<?php
namespace PHPDbLib\Database\Types\ColumnTypes;

class NumberType extends ColumnType {
    public $unsigned=false;

    public function Unsigned()
    {
        $this->unsigned=true;
    }
}
