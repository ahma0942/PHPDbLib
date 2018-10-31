<?php
namespace PHPDbLib\Database\Types\ColumnTypes;

class NumberType extends ColumnType {
    protected $unsigned=false;

    public function unsigned()
    {
        $this->unsigned=true;
        return $this;
    }
}
