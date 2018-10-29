<?php
use PHPDbLib\Database\Table;
use PHPDbLib\Database\TableExistHandler;
use PHPDbLib\Database\TableNotExistHandler;

class PHPDbLib {
	private $connection;
	private $db;
	private $tables;

	function __construct(array $config, $dbname)
	{
	    $this->db=$dbname;
		$this->connection = new mysqli($config['host'],$config['user'],$config['pass'],$dbname);
        @$tables=file_get_contents("#db/$dbname");
        if($tables===false) {
            $tables=$this->CreateObjectFromDatabase();
            file_put_contents("#db/$dbname",serialize($tables));
        }
        else $tables=unserialize($tables);

        $this->tables=$tables;
	}

	function __destruct()
	{
		$this->connection->close();
	}

	public function conn()
	{
		return $this->connection;
	}

    public function table($table)
    {
        return $this->tableExist($table)?new TableExistHandler($this->tables[$table],$this->connection):new TableNotExistHandler($table);
    }

    public function tableExist($table)
    {
        return isset($this->tables[$table]);
    }

	public function CreateObjectFromDatabase(): array
	{
		$orm=[];
		$res=$this->conn()->query("SHOW TABLES FROM $this->db")->fetch_all();

		foreach($res AS $table){
			$orm[$table[0]]=new Table($table[0]);
		}

		foreach($res AS $table)
		{
			$columns=$this->conn()->query("DESCRIBE {$table[0]}")->fetch_all();
			$keys=$this->conn()->query("
				SELECT table_name,column_name,referenced_table_name,referenced_column_name
				FROM information_schema.key_column_usage
				WHERE referenced_table_name IS NOT NULL AND table_schema = '$this->db' AND table_name='{$table[0]}'
    		")->fetch_all();

			$cols=[];
			$foreignkeys=[];
			foreach($columns AS $column) $cols[]=$column[0];
			foreach($keys AS $key) $foreignkeys[$key[1]]=[$key[0],$orm[$key[2]],$key[3]];

			$orm[$table[0]]->setColumns($cols);
			$orm[$table[0]]->setForeignKeys($foreignkeys);
		}
		return $orm;
	}
}
