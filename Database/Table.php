<?php
namespace PHPDbLib\Database;

class Table {
	public $name;
	public $columns=[];
	public $keys=[];
	public $stack = [];
	public $readonly = false;

	function __construct(string $name)
	{
		$this->name=$name;
	}

	public function setColumns(array $cols)
	{
		$this->columns=$cols;
	}

	public function setForeignKeys(array $keys)
	{
		$this->keys=$keys;
	}

	public function readonly($readonly = true)
	{
		$this->readonly = $readonly;
	}

	public function select($arr)
	{
		$this->stack['select']=$arr;
		return $this;
	}

	public function unselect($arr)
	{
		$this->stack['unselect']=$arr;
		return $this;
	}

	public function where($arr)
	{
		$this->stack['whereSQL']=$arr;
		return $this;
	}

	private function _create_nested_where_sql($arr)
	{
		$sql='(';
		$firstO=true;
		$firstA=true;
		foreach($arr as $statement)
		{
			if(is_array($statement) && is_array($statement[0])){
				$sql.=($firstO?$firstO=false:' OR ').$this->_create_nested_where_sql($statement);
			}
			else{
				if(!isset($this->stack['selects'][$statement[0]])) throw new \Exception("Cannot find column '{$statement[0]}' in where clause.");
				$sql.=($firstA?$firstA=false:' AND ')."{$this->stack['selects'][$statement[0]]}".$statement[1].'?';
				$this->stack['where'][]=$statement[2];
			}
		}
		$sql.=')';
		return $sql;
	}

	public function join($arr)
	{
		try{
			if(!is_array($arr)){
				$rand=rand();
				if(!isset($this->keys[$arr])) throw new \Exception("No foreign key reference found for table '$arr'");
				$tab=$this->keys[$arr];
				$this->_create_inner_join_sql('_ref_'.$tab[0].'_ref',$arr,$tab[1]->name,$tab[2],$rand);
				$this->_create_select_array([$arr=>$rand],$this);
			}
			elseif(is_array($arr)){
				$this->_create_nested_join_sql('_ref_'.$this->name.'_ref',$arr,$this);
			}
		} catch (\Exception $e){
			die($e->getMessage());
		}

		return $this;
	}

	private function _create_nested_join_sql($referenced_table, $arr, $class, $select = [])
	{
		$last=$referenced_table;
		$tab=$class;
		foreach($arr as $t)
		{
			$rand=rand();
			if(is_array($t)){
				$this->_create_nested_join_sql($last,$t,$tab,$select);
			}
			else{
				if(!isset($tab->keys[$t])) throw new \Exception("No foreign key reference found for table '$t'");
				$select[]=$t;
				$this->_create_select_array([$t=>$rand],$tab,implode('.',$select));
				unset($this->stack['selects'][implode('.',$select)]);
				$tab=$tab->keys[$t];
				$this->_create_inner_join_sql($last,$t,$tab[1]->name,$tab[2],$rand);
				$last=$tab[1]->name.'_'.$rand;
				$tab=$tab[1];
			}
		}
	}

	private function _create_select_array($arr,$class,$prefix="")
	{
		$doneThisBefore=false;
		if(!empty($this->stack['selects'])){
			foreach($class->columns as $col){
				if(isset($this->stack['selects'][$col]) && !isset($class->keys[$col])){
					$doneThisBefore=true;
					break;
				}
			}
		}

		foreach($class->columns as $col){
			if(!$doneThisBefore && !isset($arr[$col]) && $class->name==$this->name) $this->stack['selects'][$col]='`_ref_'.$this->name.'_ref`.`'.$col.'`';
			elseif(isset($arr[$col])){
				$t=$class->keys[$col][1]->name;
				foreach($class->keys[$col][1]->columns as $cols) {
					$this->stack['selects'][($prefix!=""?$prefix:$col).'.'.$cols]='`'.$t.'_'.$arr[$col].'`.`'.$cols.'`';
				}
			}
		}
	}

	private function _create_inner_join_sql($referenced_table,$referenced_column,$join_table,$join_column,$rand)
	{
		$this->stack['join'][]="INNER JOIN `$join_table` `{$join_table}_{$rand}` ON `$referenced_table`.`$referenced_column`=`{$join_table}_{$rand}`.`$join_column`";
	}

	public function read($perpage, $page, \mysqli $conn)
	{
		$limit = null;
		if ($perpage != null && $page == null) $limit = $perpage;
		elseif ($perpage != null && $page != null) $limit = ($perpage*$page).", ".$perpage;
		$output=[];
		try{
			if(!isset($this->stack['selects'])){
				foreach($this->columns AS $col) $this->stack['selects'][$col]="`$col`";
			}

			//SELECT
			$sql="SELECT ";
			if(!isset($this->stack['select']) || empty($this->stack['select'])){
				if(isset($this->stack['unselect']) && !empty($this->stack['unselect'])){
					foreach($this->stack['unselect'] as $sel) {
						if(!isset($this->stack['selects'][$sel])) throw new \Exception("Cannot unselect column '$sel'. Column not found.");
						unset($this->stack['selects'][$sel]);
					}
				}
				foreach($this->stack['selects'] as $val=>$key) $sql.="$key AS `$val`,";
			}
			else{
				foreach($this->stack['select'] as $sel) {
					if(!isset($this->stack['selects'][$sel])) throw new \Exception("Cannot select column '$sel'. Column not found.");
					$sql.=$this->stack['selects'][$sel]." AS `$sel`,";
				}
			}
			$sql=rtrim($sql,',').' ';

			//JOIN
			$sql.="\nFROM {$this->name} _ref_{$this->name}_ref\n".
			(isset($this->stack['join'])?' '.implode(" ",$this->stack['join']):'');

			//WHERE
			if(isset($this->stack['whereSQL'])) $sql.=" WHERE ".$this->_create_nested_where_sql($this->stack['whereSQL']);
			if(isset($this->stack['where'])){
				$table=$conn->prepare($sql.($limit ? " LIMIT $limit" : ""));
				$types=['integer'=>'i','double'=>'d','string'=>'s'];
				$bind=[''];
				foreach($this->stack['where'] AS $w) {
					$bind[0].=$types[gettype($w)];
					$bind[]=&$w;
				}
				call_user_func_array(array($table,'bind_param'),$bind);
				$table->execute();
				$table=$table->get_result();
			}
			else $table=$conn->query($sql.($limit ? " LIMIT $limit" : ""));

			$i=-1;
			while($row=$table->fetch_assoc()){
				$i++;
				foreach($row AS $k=>$v){
					if(strpos($k,'.')!==false){
						$keys=explode('.',$k);
						$main=array_shift($keys);
						if(!isset($output[$i][$main])) $output[$i][$main]=[];
						$t=&$output[$i][$main];
						foreach($keys as $key) $t=&$t[$key];
						$t=$v;
					}
					else $output[$i][$k]=$v;
				}
			}
		} catch(\Exception $e){
			die($e->getMessage());
		}

		$this->stack=[];
		return $output;
	}

	public function insert($cols, $arr, \mysqli $conn)
	{
		$sql = "INSERT INTO `".$this->name."`(`".implode('`,`', $cols)."`)\n";
		$sql .= "VALUES ";
		if (is_array($arr[0])) {
			for($i = 0; $i < count($arr); $i++) {
				$sql .= "(";
				foreach($arr[$i] AS $val) $sql .= "'".mysqli_real_escape_string($conn, $val)."',";
				$sql = rtrim($sql,',').")";
				if ($i == count($arr)-1) $sql .= ";\n";
				else $sql .= ",\n";
			}
		} else {
			$sql .= "(";
			foreach($arr AS $val) $sql .= "'".mysqli_real_escape_string($conn, $val)."',";
			$sql = rtrim($sql,',').");\n";
		}
		$this->execute($sql, $conn);
		return mysqli_affected_rows($conn);
	}

	public function checkInsert($cols, $arr, $cols2, $arr2, \mysqli $conn)
	{
		$sql = "INSERT INTO `".$this->name."`(`".implode('`,`', $cols)."`)\n";
		$sql .= "SELECT *  FROM (SELECT ";
		foreach($arr AS $val) $sql .= "'".mysqli_real_escape_string($conn, $val)."',";
		$sql = rtrim($sql,',').") AS tmp\n";
		$sql .= "WHERE NOT EXISTS (\n\t";
		$sql .= "SELECT id FROM `".$this->name."` WHERE ";
		$len = count($cols2);
		for($i = 0; $i<$len; $i++) $sql .= "`".$cols2[$i]."`='".mysqli_real_escape_string($conn, $arr2[$i])."' AND ";
		$sql = substr($sql, 0, -5)."\n) LIMIT 1;";
		$this->execute($sql, $conn);
		return mysqli_affected_rows($conn);
	}

	public function exist($cols, $arr, \mysqli $conn)
	{
		$sql = "SELECT 1 FROM `".$this->name."` WHERE ";
		$len = count($cols);
		for($i = 0; $i<$len; $i++) $sql .= "`".$cols[$i]."`='".mysqli_real_escape_string($conn, $arr[$i])."' AND ";
		$sql = substr($sql, 0, -5)."LIMIT 1;";
		$this->execute($sql, $conn);
		return mysqli_affected_rows($conn) > 0 ? true : false;
	}

	public function count($cols, $arr, \mysqli $conn)
	{
		$sql = "SELECT 1 FROM `".$this->name."` WHERE ";
		$len = count($cols);
		for($i = 0; $i<$len; $i++) $sql .= "`".$cols[$i]."`='".mysqli_real_escape_string($conn, $arr[$i])."' AND ";
		$sql = substr($sql, 0, -5).";";
		$this->execute($sql, $conn);
		return mysqli_affected_rows($conn);
	}

	public function update($arr, \mysqli $conn)
	{
		throw new \Exception("Not implemented");
	}

	public function delete($arr, \mysqli $conn)
	{
		throw new \Exception("Not implemented");
	}

	public function execute($sql, \mysqli $conn)
	{
		if ($this->readonly) echo $sql;
		else $conn->query($sql);
		if($conn->error) throw new \Exception($conn->error);
	}
}
