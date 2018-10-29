<?php
namespace PHPDbLib\Database;

class TableExistHandler {
    private $table;
    private $connection;

    function __construct($table,$connection)
    {
        $this->table=$table;
        $this->connection=$connection;
    }

    public function select($arr)
    {
        $this->table->select($arr);
        return $this;
    }

    public function unselect($arr)
    {
        $this->table->unselect($arr);
        return $this;
    }

    public function where($arr)
    {
        $this->table->where($arr);
        return $this;
    }

    public function join($arr)
    {
        $this->table->join($arr);
        return $this;
    }

    public function read()
    {
        return $this->table->read($this->connection);
    }
}
