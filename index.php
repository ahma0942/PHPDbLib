<?php
include "Database/Database.php";
include "Database/Table.php";
include "Connection.php";

$config=[
	'host'=>'localhost',
	'user'=>'root',
	'pass'=>'',
	'db'=>'test',
];

$conn = new Connection($config);
@$db=file_get_contents('db');
if($db===false) {
	$db=new Database($config['db'],$conn->CreateObjectFromDatabase());
	file_put_contents('db',serialize($db));
}
else $db=unserialize($db);

//print_r($db->table('test')->read($conn));
//print_r($db->table('test')->join('test2_id')->read($conn));
//print_r($db->table('test')->join(['test2_id','test3_id'])->read($conn));
//print_r($db->table('test')->join([['test2_id'],['test3_id']])->read($conn));
//print_r($db->table('test')->join([['test2_id',['test2_reference1'],['test2_reference2'],['test3_id']],['test3_id']])->read($conn));
//print_r($db->table('test')->select(['id','test2_id.id'])->join(['test2_id'])->read($conn));
//print_r($db->table('test')->unselect(['id','test2_id.id'])->join(['test2_id'])->read($conn));
//print_r($db->table('test')->where([[['id','>','0'],['id','<','5']],[['id','<','0'],['id','>','5']]])->join(['test2_id'])->read($conn));
