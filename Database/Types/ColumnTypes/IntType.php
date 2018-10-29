<?php
namespace PHPDbLib\Database\Types\ColumnTypes;

class IntType extends NumberType {
    public $ai=false;

    public function AutoIncrement(){
        $this->ai=true;
    }
}
