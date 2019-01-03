<?php
if(!Database::columnExists('users','custom_556'))
	Database::createColumn('users','custom_556','varchar(100)');

if(!Database::columnExists('students','custom_53'))
	Database::createColumn('students','custom_53','varchar(100)');

if(!Database::columnExists('users','custom_200000001'))
	Database::createColumn('users','custom_200000001','varchar(100)');

?>
