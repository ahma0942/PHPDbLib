<?php
namespace PHPDbLib\Database;

use PHPDbLib\Database\Types\ColumnTypes\AsciiType;
use PHPDbLib\Database\Types\ColumnTypes\ColumnType;
use PHPDbLib\Database\Types\ColumnTypes\DecimalType;
use PHPDbLib\Database\Types\ColumnTypes\IntType;
use PHPDbLib\Database\Types\ColumnTypes\ListType;
use PHPDbLib\Database\Types\ColumnTypes\NoLengthType;
use PHPDbLib\Database\Types\ColumnTypes\NumberType;
use PHPDbLib\Database\Types\DatabaseTypes;
use PHPDbLib\Database\Types\Enums\ForeignKeyOptions;

class TableNotExistHandler {
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

    public function create($callable)
    {
        $dbt=new DatabaseTypes();
        $callable($dbt);
        $keys=[];
        $cols=[];
        $foreigns=[];
        $sql="";
        foreach($dbt->getStack() as $obj){
            $arr=$obj->getObjectVars();
            $cols[]=$arr['name'];
            $sql.='`'.$arr['name']."` ".$arr['type'];

            if($obj instanceof DecimalType){
                $sql.="({$arr['length']},{$arr['precision']})";
            }
            elseif($obj instanceof ListType){
                $sql.="(";
                foreach($arr['list'] as $val) $sql.="'".mysqli_real_escape_string($this->connection,$val)."',";
                $sql=rtrim($sql,',').")";
            }
            elseif($obj instanceof ColumnType){
                if(!($obj instanceof NoLengthType)) $sql.="({$arr['length']})";
            }

            if($obj instanceof AsciiType){
                if($arr['charset']) $sql.=" CHARACTER SET {$arr['charset']}";
                if($arr['collation']) $sql.=" COLLATE {$arr['collation']}";
            }
            if($obj instanceof NumberType && $arr['unsigned']) $sql.=" UNSIGNED";
            if(!$arr['null'] || $arr['ai']) $sql.=" NOT NULL";
            if($obj instanceof IntType && $arr['ai']) $sql.=" AUTO_INCREMENT";
            if($arr['default'] != '' || $arr['default']===0) $sql.=" DEFAULT '".str_replace("'", "\'", $arr['default'])."'";

            if($arr['index']){
                if($arr['index']==0) $keys['index'][]=$arr['name'];
                else $arr['index'][]=[$arr['name'],$arr['index']];
            }
            elseif($arr['unique']) $keys['unique'][]=$arr['name'];
            elseif($arr['foreign']) $keys['foreign'][$arr['name']]=$arr['foreign'];
            elseif($arr['primary']) $keys['primary'][]=$arr['name'];

            $sql.=",\n";
        }
        foreach($keys as $key=>$args)
        {
            if($key=='primary') $sql.='PRIMARY KEY(`'.implode('`,`',$args).'`),';
            elseif($key=='foreign'){
                foreach($args as $col=>$arr){
                    list($table,$column)=explode('.',$arr[0]);
                    $sql.="FOREIGN KEY fk_$col(`$col`) REFERENCES `$table`(`$column`)";
                    $sql.=" ON DELETE ".ForeignKeyOptions::getValue($arr[1]);
                    $sql.=" ON UPDATE ".ForeignKeyOptions::getValue($arr[2]).",";
                    $foreigns[$col]=[$this->table,$table,$column];
                }
            }
            elseif($key=='index'){
                $sql.="INDEX(";
                foreach($args as $arg){
                    if(is_array($arg)) $sql.="{$arg[0]}({$arg[1]}),";
                    else $sql.=$arg.",";
                }
                $sql=rtrim($sql,',');
                $sql.="),";
            }
            elseif($key=='unique') $sql.='UNIQUE(`'.implode('`,`',$args).'`),';
        }
        $sql=rtrim($sql,',');
        $table=new Table($this->table);
        $table->setColumns($cols);
        $table->setForeignKeys($foreigns);
        $this->tables[$this->table]=$table;
        try{
            $this->tables[$this->table]->create($this->connection,$sql);
        } catch (\Exception $e){
            die($e->getMessage());
        }
        file_put_contents(__DIR__."/../#db/".$this->db,serialize($this->tables));
    }
}
