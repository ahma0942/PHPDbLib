<?php
namespace PHPDbLib\Database\Types\ColumnTypes;

class AsciiType extends ColumnType {
    protected $charset;
    protected $collation;

    public function charset($charset)
    {
        $this->charset=$charset;
        return $this;
    }

    public function collation($collation)
    {
        $this->collation=$collation;
        return $this;
    }
}
