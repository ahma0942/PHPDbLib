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

    function __construct(array $config, $dbname = false, $readonly = false)
    {
        $this->readonly = $readonly;
        $this->connection = new mysqli($config['host'],$config['user'],$config['pass']);
        if ($dbname) $this->initDB($dbname);
    }

    function __destruct()
    {
        $this->connection->close();
    }

    public function reset()
    {
        if (file_exists(dirname(__FILE__) . '/#db')) {
            $dir = new DirectoryIterator(dirname(__FILE__) . '/#db');
            foreach ($dir as $fileinfo) {
                if (!in_array($fileinfo->getFilename(), ['.htaccess','.','..'])) {
                    unlink(dirname(__FILE__).'/#db/'.$fileinfo->getFilename());
                }
            }
        }
    }

    public function initDB($dbname)
    {
        $this->db=$dbname;
        $this->connection->select_db($dbname);
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

    private function isDbInit()
    {
        if (!$this->db) die('Database not Initialized');
    }

    public function conn()
    {
        return $this->connection;
    }

    public function exec($sql)
    {
        return $this->connection->query($sql);
    }

    public function create($table, $callable)
    {
        $this->isDbInit();
        $this->tables = $this->d->create($table, $callable);
        return $this;
    }

    public function readonly($readonly = true)
    {
        $this->isDbInit();
        $this->d->readonly($readonly);
        return $this;
    }

    public function delete($table)
    {
        $this->isDbInit();
        return $this->d->delete($table);
    }

    public function update()
    {
        $this->isDbInit();
        return $this->d->update();
    }

    public function read()
    {
        $this->isDbInit();
        return $this->d->read();
    }

    public function execute()
    {
        $this->isDbInit();
        return $this->d->execute();
    }

    public function exist($table)
    {
        $this->isDbInit();
        return isset($this->tables[$table]);
    }

    public function table($table)
    {
        $this->isDbInit();
        return new TableActions($table,$this->tables,$this->connection,$this->db);
    }

    public function CreateObjectFromDatabase(): array
    {
        $this->isDbInit();
        $orm=[];
        $res=$this->conn()->query("SHOW TABLES FROM `$this->db`")->fetch_all();

        $foreignkeys=[];
        foreach($res AS $table) $orm[$table[0]]=new Table($table[0]);
        foreach($res AS $table)
        {
            $columns=$this->conn()->query("DESCRIBE {$table[0]}")->fetch_all();
            $keys=$this->conn()->query("
                SELECT table_name,column_name,referenced_table_name,referenced_column_name
                FROM information_schema.key_column_usage
                WHERE referenced_table_name IS NOT NULL AND table_schema = '$this->db' AND table_name='{$table[0]}'
            ")->fetch_all();

            $cols=[];
            foreach($columns AS $column) $cols[]=$column[0];
            foreach($keys AS $key) {
                $foreignkeys[$key[0]][$key[1]]=[$key[0],$key[2],$key[3]];
                if (isset($foreignkeys[$key[2]][$key[3]]) && $foreignkeys[$key[2]][$key[3]][0]!==false){
                    $foreignkeys[$key[2]][$key[3].".".$key[0].".".$key[1]]=[$key[2],$key[0],$key[1]];
                    $foreignkeys[$key[2]][$key[3].".".$foreignkeys[$key[2]][$key[3]][1].".".$foreignkeys[$key[2]][$key[3]][1]]=[$foreignkeys[$key[2]][$key[3]][0],$foreignkeys[$key[2]][$key[3]][1],$foreignkeys[$key[2]][$key[3]][2]];
                    $foreignkeys[$key[2]][$key[3]]=[
                        false,
                        $key[3].".".$foreignkeys[$key[2]][$key[3]][1].".".$foreignkeys[$key[2]][$key[3]][1],
                        $key[3].".".$key[0].".".$key[1],
                    ];
                } elseif (isset($foreignkeys[$key[2]][$key[3]]) && $foreignkeys[$key[2]][$key[3]][0]===false){
                    $foreignkeys[$key[2]][$key[3]][]=$key[3].".".$key[0].".".$key[1];
                    $foreignkeys[$key[2]][$key[3].".".$key[0].".".$key[1]]=[$key[2],$key[0],$key[1]];
                }
                else $foreignkeys[$key[2]][$key[3]]=[$key[2],$key[0],$key[1]];
            }
            $orm[$table[0]]->setColumns($cols);
        }
        foreach($foreignkeys as $tab=>$fk) $orm[$tab]->setForeignKeys($fk);
        return $orm;
    }
}
