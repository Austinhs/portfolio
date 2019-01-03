<?php

if(Database::$type === 'mssql') {
	$sql = "ALTER TABLE
					scheduling_lunch_rule_detail
				ALTER COLUMN
					room_id BIGINT NULL

	";

	Database::query($sql);
	
	$sql = "ALTER TABLE
					scheduling_lunch_rule_detail
				ALTER COLUMN
					staff_id BIGINT NULL
	";

	Database::query($sql);
}
