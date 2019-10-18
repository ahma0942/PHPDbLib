<?php
namespace PHPDbLib\Database;

class DatabaseActions {
    private $tables;
    private $conn;
    private $db;
    private $d;

    function __construct($tables,$conn,$db)
    {
        $this->tables=$tables;
        $this->db=$db;
        $this->conn=$conn;
        $this->d = new Database($tables, $conn, $db);
    }

    public function delete($table)
    {
        $this->d->delete($table);
        return $this;
    }

    public function create($table, $callable)
    {
        return $this->d->create($table, $callable);
    }

    public function update()
    {
        $this->d->update();
        return $this;
    }

    public function read()
    {
        $this->d->update();
        return $this;
    }

    public function execute()
    {
        $this->d->execute();
        return $this;
    }
}
