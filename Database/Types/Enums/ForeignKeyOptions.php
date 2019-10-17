<?php
namespace PHPDbLib\Database\Types\Enums;

abstract class ForeignKeyOptions {
    const RESTRICT=1;
    const CASCADE=2;
    const SET_NULL=3;
    const NO_ACTION=4;
    const SET_DEFAULT=5;

    public static function getValue($i)
    {
        switch($i){
            case 1: return 'RESTRICT';
            case 2: return 'CASCADE';
            case 3: return 'SET NULL';
            case 4: return 'NO ACTION';
            case 5: return 'SET DEFAULT';
        }
        return 0;
    }
}
