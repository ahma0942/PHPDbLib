<?php
namespace PHPDbLib\Database\Types\StringTypes;

use PHPDbLib\Database\Types\ColumnTypes\NoLengthType;

class Text extends NoLengthType {
    protected $type="TEXT";

    function __construct($name)
    {
        parent::__construct($name);
    }
}
