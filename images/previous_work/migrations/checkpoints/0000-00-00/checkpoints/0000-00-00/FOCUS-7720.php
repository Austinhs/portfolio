<?php

$database_type = Database::$type;
$database_type = strtolower($database_type);
if($database_type === 'mssql') {
	$sql = 'alter table gradebook_custom_grades alter column display varchar(40);';
}
else{
	$sql = 'alter table gradebook_custom_grades alter column display type varchar(40);';
}
Database::query($sql);
