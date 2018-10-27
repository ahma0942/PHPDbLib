<?php
class Database {
	private $tables;
	private $name;

	function __construct(string $name, array $tables)
	{
		$this->name=$name;
		$this->tables=$tables;
	}

	public function table($table)
	{
		try{
			return $this->getTable($table);
		} catch (Exception $e){
			die($e->getMessage());
		}
	}

	private function getTable($table)
	{
		if(!isset($this->tables[$table])) throw new Exception("Unknown table $table");
		return $this->tables[$table];
	}
}
