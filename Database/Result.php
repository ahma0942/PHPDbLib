<?php
namespace PHPDbLib\Database;

class Result implements \ArrayAccess {
    private $table;
    private $conn;
    private $data;

    function __construct($table, $columns, $keys, $conn, $data = []) {
        $this->table = new Table($table);
        $this->table->setColumns($columns);
        $this->table->setForeignKeys($keys);
        $this->conn = $conn;
        $this->data = $data;
    }

    public function getArray() {
        $d = [];
        foreach($this->data AS $k=>$v) {
            if ($v instanceof \ArrayAccess) $d[$k] = $v->getArray();
            else $d[$k] = $v;
        }
        return $d;
    }

    public function update() {
        $sql = "UPDATE `{$this->table->name}`";
        $sql .= "\nSET";
        foreach($this->data AS $k=>$v) {
            if($v instanceof \ArrayAccess) continue;
            if(gettype($v) == 'NULL') $v = "NULL";
            else $v = "'".mysqli_real_escape_string($this->conn, $v)."'";
            $sql .= "\n\t`$k`=$v,";
        }
        $sql = rtrim($sql,',')."\n";
        $sql .= "WHERE `id`={$this->data['id']}";
        $this->table->execute($sql, $this->conn);
        return mysqli_affected_rows($this->conn);
    }

    public function offsetSet($offset, $value): void {
        if (is_null($offset)) $this->data[] = $value;
        else $this->data[$offset] = $value;
    }

    public function offsetExists($offset): bool {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset): void {
        unset($this->data[$offset]);
    }

    public function &offsetGet($offset): mixed {
        return $this->data[$offset];
    }
}
