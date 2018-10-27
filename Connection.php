<?php
class Connection {
	private $connection;
	private $dbname;

	function __construct(array $config)
	{
		$this->dbname = $config['db'];
		$this->connection = new mysqli($config['host'],$config['user'],$config['pass'],$config['db']);
	}

	function __destruct()
	{
		$this->connection->close();
	}

	public function conn()
	{
		return $this->connection;
	}

	public function CreateObjectFromDatabase(): array
	{
		$orm=[];
		$res=$this->conn()->query("SHOW TABLES FROM $this->dbname")->fetch_all();

		foreach($res AS $table){
			$orm[$table[0]]=new Table($table[0]);
		}

		foreach($res AS $table)
		{
			$columns=$this->conn()->query("DESCRIBE {$table[0]}")->fetch_all();
			$keys=$this->conn()->query("
				SELECT table_name,column_name,referenced_table_name,referenced_column_name
				FROM information_schema.key_column_usage
				WHERE referenced_table_name IS NOT NULL AND table_schema = '$this->dbname' AND table_name='{$table[0]}'
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
