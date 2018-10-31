<?php
namespace PHPDbLib\Database\Types\ColumnTypes;

use PHPDbLib\Database\Types\Enums\ForeignKeyOptions;

class ColumnType {
    protected $name;
    protected $length;
    protected $null;
    protected $default;
    protected $primary;
    protected $foreign;
    protected $unique;
    protected $index;

    function __construct($name,$length)
    {
        $this->name=$name;
        $this->length=$length;
    }

    public function primary()
    {
        $this->primary=true;
    }

    public function foreign($tabcol,$ondelete=ForeignKeyOptions::RESTRICT,$onupdate=ForeignKeyOptions::RESTRICT)
    {
        $this->foreign=[$tabcol,$ondelete,$onupdate];
    }

    public function unique()
    {
        $this->unique=true;
    }

    public function index($num=0)
    {
        $this->index=$num;
    }

    public function default($default)
    {
        if($default===false) $default=0;
        $this->default=$default;
        return $this;
    }

    public function nullable()
    {
        $this->null=true;
        return $this;
    }

    public function getObjectVars()
    {
        return get_object_vars($this);
    }
}
