<?php
namespace PHPDbLib\Database\Types\ListTypes;

use PHPDbLib\Database\Types\ColumnTypes\ListType;

class Enum extends ListType {
    protected $type="ENUM";

    function __construct($name,$list)
    {
        parent::__construct($name,$list);
    }
}
