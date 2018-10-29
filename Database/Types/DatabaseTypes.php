<?php
namespace PHPDbLib\Database\Types;

use PHPDbLib\Database\Types\IntegerTypes\Integer;

class DatabaseTypes {
    private $stack;

    public function int(string $name, int $length=10){
        $int=new Integer($name,$length);
        $this->stack[]=$int;
        return $int;
    }

    public function getStack(){
        return $this->stack;
    }
}
