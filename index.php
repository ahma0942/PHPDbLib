<?php
include "PHPDbLib.php";

use PHPDbLib\Database\Types\DatabaseTypes;
use PHPDbLib\Database\Types\Enums\ForeignKeyOptions;

spl_autoload_register(function($class) {
	if(file_exists(str_replace('\\','/',__DIR__."/$class.php"))) include str_replace('\\','/',__DIR__."/$class.php");
});

$config = [
	'host'=>'localhost',
	'user'=>'root',
	'pass'=>'',
];

$db = new PHPDbLib($config, 'test');

/*

if($db->table('users')) $db->delete('users');

if(!$db->table('users')){
	$db->create('users',function(DatabaseTypes $types){
		$types->int("id",10)->unsigned()->autoIncrement();
		$types->varchar("username",30)->unique();
		$types->varchar("password",50);
	});
	echo "Created table users\n";
}

if(!$db->table('messages')){
	$db->create('messages',function(DatabaseTypes $types){
		$types->int("id",10)->unsigned()->autoIncrement();
		$types->int("user_id",10)->unsigned()->foreign('users.id',ForeignKeyOptions::RESTRICT,ForeignKeyOptions::RESTRICT);
		$types->text("message");
	});
	echo "Created table messages\n";
}

$db->execute();
//$db->insert('users')->assoc(["username","password"],["131","Test1234"]);
print_r($db->table('messages')->join('user_id')->read());
print_r($db->table('test')->join('test2_id')->read());
print_r($db->table('test')->join(['test2_id','test3_id'])->read());
print_r($db->table('test')->join([['test2_id'],['test3_id']])->read());
print_r($db->table('test')->join([['test2_id',['test2_reference1'],['test2_reference2'],['test3_id']],['test3_id']])->read());
print_r($db->table('test')->select(['id','test2_id.id'])->join(['test2_id'])->read());
print_r($db->table('test')->unselect(['id','test2_id.id'])->join(['test2_id'])->read());
print_r($db->table('test')->where([[['id','>','0'],['id','<','5']],[['id','<','0'],['id','>','5']]])->join(['test2_id'])->read());
*/

