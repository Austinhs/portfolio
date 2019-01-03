<?php

if(!Database::columnExists("custom_fields", "requires_authentication"))
{
	// Add "requires_authentication" column to "custom_fields" table
	Database::createColumn("custom_fields", "requires_authentication", "int");

	// Update existing rows to 1 to preserve old behavior
	Database::query("UPDATE custom_fields SET requires_authentication=1 WHERE type='signature'");

	// Default new rows to 1
	if(Database::$type === "mssql")
	{
		Database::query("ALTER TABLE custom_fields ADD DEFAULT 1 FOR requires_authentication");
	}
	else
	{
		Database::query("ALTER TABLE custom_fields ALTER COLUMN requires_authentication SET DEFAULT 1");
	}
}

// Make source_class and source_id columns of "signatures" table nullable
Database::changeColumnType("signatures", "source_class", "varchar", "255", true);
Database::changeColumnType("signatures", "source_id", "bigint", "", true);
