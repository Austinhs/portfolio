<?php

if(Database::$type === 'mssql')
{
	Database::query("IF OBJECT_ID('FieldOptionLabel', 'FN') IS NOT NULL DROP FUNCTION dbo.FieldOptionLabel");

	$sql = "
		CREATE FUNCTION FieldOptionLabel (@id SQL_VARIANT) RETURNS VARCHAR(MAX) AS
			BEGIN
				DECLARE @label AS VARCHAR(MAX);
				SELECT @label = label FROM custom_field_select_options WITH (NOLOCK) WHERE ({{is_int:@id}}) AND id = CAST(@id AS BIGINT);
				RETURN @label;
			END
	";
	Database::query(Database::preprocess($sql));
}
