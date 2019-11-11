<?php
namespace PHPDbLib\Database;

class Table {
	public $tables;
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
		return $this;
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

	public function noCheckJoin($arr)
	{
		if (!is_array($arr)) throw new \Exception("Expected array, got '".gettype($arr)."'");
		try{
			if(!is_array($arr[0])){
				$rand=rand();
				$tab=$this->keys[$arr];
				$this->_create_inner_join_sql_noCheck('_ref_'.$tab[0].'_ref',$arr,$tab[1],$tab[2],$rand);
				$this->_create_select_array([$arr=>$rand],$this);
			}
			elseif(is_array($arr[0])){
				$this->_create_nested_join_sql('_ref_'.$this->name.'_ref',$arr,$this);
			}
		} catch (\Exception $e){
			die($e->getMessage());
		}
		return $this;
	}

	public function join($arr)
	{
		try{
			if(!is_array($arr)){
				$rand=rand();
				if(strpos($arr,'|')!==false){
					$opt=explode('|',$arr);
					$arr=array_shift($opt);
				} else $opt=[];
				if(!isset($this->keys[$arr])) throw new \Exception("No foreign key reference found for column '$arr'");
				if(!isset($this->keys[$arr][2])) throw new \Exception("Reference for '$arr' is too ambitious. Use the following:\n".$this->keys[$arr][0]."\n".$this->keys[$arr][1]);
				$tarr=explode('.',$arr);
				$joined_table=null;
				if(!isset($this->keys[$tarr[0]])) $tarr[0]=$arr;
				if(strpos($arr,'.')!==false) $joined_table=explode('.',$arr)[1];
				$tab=$this->keys[$arr];
				$this->_create_inner_join_sql('_ref_'.$tab[0].'_ref',$tarr[0],$tab[1],$tab[2],$rand,in_array('NULL',$opt)?true:false);
				$this->_create_select_array([$tarr[0]=>$rand],$this,"",$joined_table);
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
				if(strpos($t,'|')!==false){
					$opt=explode('|',$t);
					$t=array_shift($opt);
				} else $opt=[];
				if(!isset($tab->keys[$t])) throw new \Exception("No foreign key reference found for column '$t'");
				if(!isset($tab->keys[$t][2])) throw new \Exception("Reference for '$t' is too ambitious. Use the following:\n".$tab->keys[$t][0]."\n".$tab->keys[$t][1]);
				$tt=explode('.',$t);
				$joined_table=null;
				if(!isset($tab->keys[$tt[0]])) $tt[0]=$t;
				if(strpos($t,'.')!==false) $joined_table=explode('.',$t)[1];
				$select[]=$tt[0];
				$this->_create_select_array([$tt[0]=>$rand],$tab,implode('.',$select),$joined_table);
				unset($this->stack['selects'][implode('.',$select)]);
				$this->_create_inner_join_sql($last,$tt[0],$tab->keys[$t][1],$tab->keys[$t][2],$rand,in_array('NULL',$opt)?true:false);
				$last=$tab->keys[$t][1].'_'.$rand;
				$tab=$tab->tables[$tab->keys[$t][1]];
			}
		}
	}

	public function addTables($tables)
	{
		$this->tables = $tables;
	}

	private function _create_select_array($arr,$class,$prefix="",$table=null)
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
				$t=$table ? $table : $class->keys[$col][1];
				foreach($this->tables[$t]->columns as $cols) {
					$this->stack['selects'][($prefix!=""?$prefix:$col).'.'.$cols]='`'.$t.'_'.$arr[$col].'`.`'.$cols.'`';
				}
			}
		}
	}

	private function _create_inner_join_sql($referenced_table,$referenced_column,$join_table,$join_column,$rand,$null=false)
	{
		if(!$null) $this->stack['join'][]="INNER JOIN `$join_table` `{$join_table}_{$rand}` ON `$referenced_table`.`$referenced_column`=`{$join_table}_{$rand}`.`$join_column`";
		else $this->stack['join'][]="INNER JOIN `$join_table` `{$join_table}_{$rand}` ON (`$referenced_table`.`$referenced_column`=`{$join_table}_{$rand}`.`$join_column` OR (`$referenced_table`.`$referenced_column` IS NULL AND `{$join_table}_{$rand}`.`$join_column` IS NULL))";
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
			(isset($this->stack['join'])?implode("\n",$this->stack['join']):'');

			//WHERE
			if(isset($this->stack['whereSQL'])) $sql.=" WHERE ".$this->_create_nested_where_sql($this->stack['whereSQL']);
			$sql = $sql.($limit ? " LIMIT $limit" : "");
			if ($this->readonly) {
				echo "<h2><pre>$sql</pre></h2>";
				return;
			}
			if(isset($this->stack['where'])){
				$table=$conn->prepare($sql);
				$types=['integer'=>'i','double'=>'d','string'=>'s'];
				$bind=[''];
				for($i=0; $i<count($this->stack['where']); $i++){
					$bind[0].=$types[gettype($this->stack['where'][$i])];
					$bind[]=&$this->stack['where'][$i];
				}
				call_user_func_array(array($table,'bind_param'),$bind);
				$table->execute();
				$table=$table->get_result();
			}
			else $table=$conn->query($sql);

			$i=-1;
			while($row=$table->fetch_assoc()){
				$i++;
				$output[$i] = new Result($this->name, $this->columns, $this->keys, $conn);
				foreach($row AS $k=>$v){
					if(strpos($k,'.')!==false){
						$keys=explode('.',$k);
						$main=array_shift($keys);
						if(!isset($output[$i][$main])) $output[$i][$main] = new Result($this->keys[$main][1], $this->columns, $this->keys, $conn);
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
		return new Result($this->name, $this->columns, $this->keys, $conn, $output);
	}

	public function insert($cols=[], $arr=[], \mysqli $conn)
	{
		$sql = "INSERT INTO `".$this->name."`".(empty($cols) ? " " : "(`".implode('`,`', $cols)."`)\n");
		$sql .= "VALUES ";
		if(!empty($arr)){
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
		} else $sql.="();";
		$this->execute($sql, $conn);
		return count($arr) < 2 ? $conn->insert_id : mysqli_affected_rows($conn);
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
		if ($this->readonly) echo "<h3><pre>$sql</pre></h3>";
		else $conn->query($sql);
		if($conn->error) throw new \Exception($conn->error);
	}
}
