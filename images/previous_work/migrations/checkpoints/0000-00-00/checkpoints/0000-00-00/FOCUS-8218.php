<?php

if(Database::tableExists('STUDENTS_BILLED_DATES') && Database::$type == 'mssql') {

	$queries = [
		"ALTER TABLE STUDENTS_BILLED_DATES ADD TMP_DATE DATETIME",
		"UPDATE STUDENTS_BILLED_DATES SET TMP_DATE = CAST(DATE AS DATETIME)",
		"ALTER TABLE STUDENTS_BILLED_DATES DROP COLUMN DATE",
		"EXEC sp_RENAME 'students_billed_dates.tmp_date', 'date', 'COLUMN'"
	];

	foreach($queries as $query) {
		Database::query($query);
	}
}


?>
