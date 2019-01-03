<?php
global $DatabaseType;

if ($DatabaseType === 'postgres') {
	Database::query("
		create or replace function istimestamp(s varchar) returns boolean as $$
		begin
			perform s::timestamp;
			return true;
		exception when others then
			return false;
		end;
		$$ language plpgsql;
	");
}

$date_cols = Database::get("
	SELECT field_id, column_name
	FROM custom_field_log_columns
	WHERE type = 'date'
");

foreach ($date_cols as $key => $val) {

	$col = $val['COLUMN_NAME'];
	$fid = $val['FIELD_ID'];

	// MSSQL has a disparity between the isdate function and its date casting. isdate() returns true for values
	// that look like [1-12]/[1-31]/0[00-99], but casting it to a date will fail. Postgres will cast these dates
	// as having a year of 00XX. We need to try casting with MSSQL to avoid crashing. The try_cast function
	// is only available in SQL Server 2012 and later, however.
	$isDate = $DatabaseType == 'postgres' ? "istimestamp({$col}) = true" : "TRY_CAST({$col} as datetime) IS NOT NULL";

	// MSSQL's default date -> string format is Mon DD YYYY HH:MM a. We need to convert from a string to a date, then
	// back to a string so we can tell it what format to use. 20 is the code for the format we want
	// https://docs.microsoft.com/en-us/sql/t-sql/functions/cast-and-convert-transact-sql
	$cast = $DatabaseType == 'postgres' ? "CAST({$col} as timestamp)"  : "CONVERT(varchar, CAST({$col} AS DATETIME), 20)";

	Database::query("
		UPDATE custom_field_log_entries
		SET {$col} = {$cast}
		WHERE field_id = {$fid}
			AND {$isDate}			
			AND {$col} IS NOT NULL
	");
}
