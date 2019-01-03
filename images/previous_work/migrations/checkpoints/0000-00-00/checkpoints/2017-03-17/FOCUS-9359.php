<?php

if(Database::$type === 'mssql')
{
	$identityColumn = Database::getIdentityColumn("custom_block");
	if(empty($identityColumn))
	{
		Database::query("ALTER TABLE custom_block DROP COLUMN id");
		Database::query("ALTER TABLE custom_block ADD id INT IDENTITY NOT NULL PRIMARY KEY");
	}
}
