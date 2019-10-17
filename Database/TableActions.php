<?php
namespace PHPDbLib\Database;

class TableActions {
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

    public function read($perpage = null, $page = null)
    {
        return $this->tables[$this->table]->read($perpage, $page, $this->connection);
    }

    public function update($arr)
    {
        $this->tables[$this->table]->update($arr, $this->connection);
    }

    public function delete($arr)
    {
        $this->tables[$this->table]->delete($arr, $this->connection);
    }

    public function insert($cols, $arr)
    {
        $this->tables[$this->table]->insert($cols, $arr, $this->connection);
    }
}
