<?php
namespace PHPDbLib\Database\Types\ColumnTypes;

class ColumnType {
    public $name;
    public $length;
    public $null=false;
    public $default;
    public $charset='utf8';
    public $collation='utf8_bin';

    function __construct($name,$length)
    {
        $this->name=$name;
        $this->length=$length;
    }
}
