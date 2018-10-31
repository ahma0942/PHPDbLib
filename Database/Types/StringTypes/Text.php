<?php
namespace PHPDbLib\Database\Types\StringTypes;

use PHPDbLib\Database\Types\ColumnTypes\AsciiType;

class Text extends AsciiType {
    protected $type="TEXT";

    function __construct($name)
    {
        parent::__construct($name,null);
    }
}
