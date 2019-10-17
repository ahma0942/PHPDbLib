<?php
namespace PHPDbLib\Database\Types\ColumnTypes;

class ListType extends AsciiType {
    protected $list=[];

    function __construct($name, $list)
    {
        parent::__construct($name,null);
        $this->list=$list;
    }
}
