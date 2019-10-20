<?php
use PHPDbLib\Database\DatabaseActions;
use PHPDbLib\Database\Database;
use PHPDbLib\Database\TableActions;
use PHPDbLib\Database\Table;

class PHPDbLib {
	private $tables;
	private $connection;
	private $db;
	private $d;
	private $readonly;

	function __construct(array $config, $dbname, $readonly = false)
	{
		$this->readonly = $readonly;
	    $this->db=$dbname;
		$this->connection = new mysqli($config['host'],$config['user'],$config['pass'],$dbname);
		if(!file_exists(__DIR__.'/#db')) {
			mkdir(__DIR__.'/#db');
			file_put_contents(__DIR__.'/#db/.htaccess','Deny from  all');
		}
        @$tables=file_get_contents(__DIR__."/#db/$dbname");
        if($tables===false) {
            $tables=$this->CreateObjectFromDatabase();
            file_put_contents(__DIR__."/#db/$dbname",serialize($tables));
        }
        else $tables=unserialize($tables);
		$this->tables=$tables;
		$this->d = new DatabaseActions($this->tables, $this->connection, $this->db);
	}

	function __destruct()
	{
		$this->connection->close();
	}

	public function conn()
	{
		return $this->connection;
	}

	public function create($table, $callable)
	{
		$this->tables = $this->d->create($table, $callable);
		return $this;
	}

	public function readonly($readonly = true)
	{
		$this->d->readonly($readonly);
		return $this;
	}

	public function delete($table)
	{
		return $this->d->delete($table);
	}

	public function update()
	{
		return $this->d->update();
	}

	public function read()
	{
		return $this->d->read();
	}

	public function execute()
	{
		return $this->d->execute();
	}

	public function exist($table)
    {
        return isset($this->tables[$table]);
    }

    public function table($table)
    {
		return new TableActions($table,$this->tables,$this->connection,$this->db);
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
