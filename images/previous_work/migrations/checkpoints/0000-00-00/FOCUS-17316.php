<?php

Migrations::depend("FOCUS-16920");

if(!Database::columnExists("standards", "cte_import"))
{
	Database::createColumn("standards", "cte_import", "varchar", "1", true);
}

if(!Database::columnExists("standard_categories_1", "cte_import"))
{
	Database::createColumn("standard_categories_1", "cte_import", "varchar", "1", true);
}

if(!Database::columnExists("standard_categories_2", "cte_import"))
{
	Database::createColumn("standard_categories_2", "cte_import", "varchar", "1", true);
}
