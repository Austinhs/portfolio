<?php

if(Database::$type === "mssql")
{
	Database::removeIdentityColumn("standard_categories_1");
	Database::removeIdentityColumn("standard_categories_2");
	Database::removeIdentityColumn("standards");
}

$sc1TitleLength       = Database::getColumns('standard_categories_1')['title']['CHARACTER_MAXIMUM_LENGTH'];
$sc2TitleLength       = Database::getColumns('standard_categories_2')['title']['CHARACTER_MAXIMUM_LENGTH'];
$standardsTitleLength = Database::getColumns('standards')['title']['CHARACTER_MAXIMUM_LENGTH'];

if($sc1TitleLength < 1024)
{
	Database::changeColumnType("standard_categories_1", "title", "varchar", "1024");
}
if($sc2TitleLength < 1024)
{
	Database::changeColumnType("standard_categories_2", "title", "varchar", "1024");
}
if($standardsTitleLength < 1024)
{
	Database::changeColumnType("standards", "title", "varchar", "1024");
}
