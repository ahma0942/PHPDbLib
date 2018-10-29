<?php
namespace PHPDbLib\Database;

use PHPDbLib\Database\Types\DatabaseTypes;

class TableNotExistHandler {
    private $table;

    function __construct($table)
    {
        $this->table=$table;
    }

    public function create($callable)
    {
        $dbt=new DatabaseTypes();
        $callable($dbt);
        print_r($dbt);
    }
}
