<?php
namespace PHPDbLib\Database\Types;

use PHPDbLib\Database\Types\BitTypes\Bit;
use PHPDbLib\Database\Types\DateAndTimeTypes\Date;
use PHPDbLib\Database\Types\DateAndTimeTypes\Datetime;
use PHPDbLib\Database\Types\DateAndTimeTypes\Time;
use PHPDbLib\Database\Types\DateAndTimeTypes\Timestamp;
use PHPDbLib\Database\Types\DateAndTimeTypes\Year;
use PHPDbLib\Database\Types\DecimalTypes\Decimal;
use PHPDbLib\Database\Types\DecimalTypes\Double;
use PHPDbLib\Database\Types\DecimalTypes\Floating;
use PHPDbLib\Database\Types\DecimalTypes\Numeric;
use PHPDbLib\Database\Types\DecimalTypes\Real;
use PHPDbLib\Database\Types\IntegerTypes\BigInt;
use PHPDbLib\Database\Types\IntegerTypes\Integer;
use PHPDbLib\Database\Types\IntegerTypes\MediumInt;
use PHPDbLib\Database\Types\IntegerTypes\SmallInt;
use PHPDbLib\Database\Types\IntegerTypes\TinyInt;
use PHPDbLib\Database\Types\ListTypes\Enum;
use PHPDbLib\Database\Types\ListTypes\Set;
use PHPDbLib\Database\Types\StringTypes\Binary;
use PHPDbLib\Database\Types\StringTypes\Blob;
use PHPDbLib\Database\Types\StringTypes\Char;
use PHPDbLib\Database\Types\StringTypes\Text;
use PHPDbLib\Database\Types\StringTypes\Varbinary;
use PHPDbLib\Database\Types\StringTypes\Varchar;

class DatabaseTypes {
    private $stack;

    /*****************************INT TYPES******************************/
    public function tinyint(string $name, int $length){
        $obj=new TinyInt($name,$length);
        $this->stack[]=$obj;
        return $obj;
    }

    public function smallint(string $name, int $length){
        $obj=new SmallInt($name,$length);
        $this->stack[]=$obj;
        return $obj;
    }

    public function mediumint(string $name, int $length){
        $obj=new MediumInt($name,$length);
        $this->stack[]=$obj;
        return $obj;
    }

    public function int(string $name, int $length){
        $obj=new Integer($name,$length);
        $this->stack[]=$obj;
        return $obj;
    }

    public function bigint(string $name, int $length){
        $obj=new BigInt($name,$length);
        $this->stack[]=$obj;
        return $obj;
    }

    /*****************************DECIMAL TYPES******************************/
    public function decimal(string $name, int $length, int $precision){
        $obj=new Decimal($name,$length,$precision);
        $this->stack[]=$obj;
        return $obj;
    }

    public function numeric(string $name, int $length=10, int $precision=10){
        $obj=new Numeric($name,$length,$precision);
        $this->stack[]=$obj;
        return $obj;
    }

    public function float(string $name, int $length=10, int $precision=10){
        $obj=new Floating($name,$length,$precision);
        $this->stack[]=$obj;
        return $obj;
    }

    public function real(string $name, int $length=10, int $precision=10){
        $obj=new Real($name,$length,$precision);
        $this->stack[]=$obj;
        return $obj;
    }

    public function double(string $name, int $length=10, int $precision=10){
        $obj=new Double($name,$length,$precision);
        $this->stack[]=$obj;
        return $obj;
    }

    /*****************************BIT TYPES******************************/
    public function bit(string $name, int $length=10){
        $obj=new Bit($name,$length);
        $this->stack[]=$obj;
        return $obj;
    }

    public function bool(string $name){
        $obj=new TinyInt($name,1);
        $this->stack[]=$obj;
        return $obj;
    }

    /*****************************DATETIME TYPES******************************/
    public function date(string $name){
        $obj=new Date($name);
        $this->stack[]=$obj;
        return $obj;
    }

    public function datetime(string $name){
        $obj=new Datetime($name);
        $this->stack[]=$obj;
        return $obj;
    }

    public function timestamp(string $name){
        $obj=new Timestamp($name);
        $this->stack[]=$obj;
        return $obj;
    }

    public function time(string $name){
        $obj=new Time($name);
        $this->stack[]=$obj;
        return $obj;
    }

    public function year(string $name, int $length){
        $obj=new Year($name,$length);
        $this->stack[]=$obj;
        return $obj;
    }

    /*****************************STRING TYPES******************************/
    public function varchar(string $name, int $length=10){
        $obj=new Varchar($name,$length);
        $this->stack[]=$obj;
        return $obj;
    }

    public function char(string $name, int $length=10){
        $obj=new Char($name,$length);
        $this->stack[]=$obj;
        return $obj;
    }

    public function binary(string $name, int $length=10){
        $obj=new Binary($name,$length);
        $this->stack[]=$obj;
        return $obj;
    }

    public function varbinary(string $name, int $length=10){
        $obj=new Varbinary($name,$length);
        $this->stack[]=$obj;
        return $obj;
    }

    public function blob(string $name){
        $obj=new Blob($name);
        $this->stack[]=$obj;
        return $obj;
    }

    public function text(string $name){
        $obj=new Text($name);
        $this->stack[]=$obj;
        return $obj;
    }

    /*****************************LIST TYPES******************************/
    public function enum(string $name, array $list){
        $obj=new Enum($name,$list);
        $this->stack[]=$obj;
        return $obj;
    }

    public function set(string $name, array $list){
        $obj=new Set($name,$list);
        $this->stack[]=$obj;
        return $obj;
    }
    
    /*****************************getStack()******************************/
    public function getStack(){
        return $this->stack;
    }
}
