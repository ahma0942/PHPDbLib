<?php
namespace PHPDbLib\Database;

class TableExistHandler {
    private $table;
    private $tables;
    private $connection;
    private $db;

    function __construct($table,$tables,$connection,$db)
    {
        $this->table=$table;
        $this->tables=$tables;
        $this->connection=$connection;
        $this->db=$db;
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

    public function delete()
    {
        $this->tables[$this->table]->delete($this->connection);
        return new TableNotExistHandler($this->table,$this->tables,$this->connection,$this->db);
    }
}
