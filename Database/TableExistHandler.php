<?php
namespace PHPDbLib\Database;

class TableExistHandler {
    private $table;
    private $tables;
    private $connection;

    function __construct($table,$tables,$connection)
    {
        $this->table=$table;
        $this->tables=$tables;
        $this->connection=$connection;
    }

    public function select($arr)
    {
        $this->tables[$this->table]->select($arr);
        return $this;
    }

    public function unselect($arr)
    {
        $this->tables[$this->table]->unselect($arr);
        return $this;
    }

    public function where($arr)
    {
        $this->tables[$this->table]->where($arr);
        return $this;
    }

    public function join($arr)
    {
        $this->tables[$this->table]->join($arr);
        return $this;
    }

    public function read()
    {
        return $this->tables[$this->table]->read($this->connection);
    }
}
