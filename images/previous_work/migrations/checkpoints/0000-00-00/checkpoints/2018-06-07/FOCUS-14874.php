<?php

Database::begin();

if (!Database::columnExists("custom_fields", "visible_on_add")) {
	Database::createColumn("custom_fields", "visible_on_add", "bigint");
}

Database::commit();