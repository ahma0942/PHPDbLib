<?php
namespace PHPDbLib\Database\Types\ListTypes;

use PHPDbLib\Database\Types\ColumnTypes\ListType;

class Set extends ListType {
    protected $type="SET";

    function __construct($name,$list)
    {
        parent::__construct($name,$list);
    }
}
