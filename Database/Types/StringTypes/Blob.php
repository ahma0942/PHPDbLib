<?php
namespace PHPDbLib\Database\Types\StringTypes;

use PHPDbLib\Database\Types\ColumnTypes\NoLengthType;

class Blob extends NoLengthType {
    protected $type="BLOB";

    function __construct($name)
    {
        parent::__construct($name);
    }
}
