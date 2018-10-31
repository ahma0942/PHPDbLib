<?php
namespace PHPDbLib\Database\Types\ColumnTypes;

class IntType extends NumberType {
    protected $ai=false;

    public function autoIncrement(){
        $this->ai=true;
        $this->primary=true;
        return $this;
    }
}
