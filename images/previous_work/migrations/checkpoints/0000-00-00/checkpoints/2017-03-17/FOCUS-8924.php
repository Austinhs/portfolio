<?php

// Update default values
Database::query("
		UPDATE custom_fields
		SET default_value='Y'
		WHERE type='checkbox' AND default_value='1'
		");
Database::query("
		UPDATE custom_field_log_columns
		SET default_value='Y'
		WHERE type='checkbox' AND default_value='1'
		");

// get all custom fields with type 'checkbox'
$all_custom_fields = Database::get("SELECT column_name, source_class FROM custom_fields WHERE type='checkbox'");

// Update existing '1' records to 'Y'
$tables_to_update = [
	'SISStudent' => [ 'students', 'students_form_records' ],
	'SISUser'    => [ 'users', 'users_form_records' ],
];

foreach ($tables_to_update as $source_class => $tables)
{
	foreach ($all_custom_fields as $column)
	{
		if($source_class !== $column["SOURCE_CLASS"]) {
			continue;
		}

		$column = $column["COLUMN_NAME"];

		foreach($tables as $table) {
			if (Database::columnExists($table, $column))
			{
				if (substr(Database::getColumnType($table, $column), 0, 4) !== "char")
				{
					// drop view so we can alter the column
					if (Database::$type === "mssql")
					{
						Database::query("IF EXISTS (SELECT '' FROM sys.views WHERE name='{$table}_view') DROP VIEW {$table}_view");
					}
					else
					{
						Database::query("DROP VIEW IF EXISTS {$table}_view");
					}

					Database::changeColumnType($table, $column, "char");

					// restore the dropped view
					if (substr($table, 0, 8) === "students")
					{
						SISStudent::refreshViews();
					}
					elseif (substr($table, 0, 5) === "users")
					{
						SISUser::refreshViews();
					}
				}
				Database::query("
						UPDATE {$table}
						SET {$column} = 'Y'
						WHERE {$column} = '1'
						");
			}
		}
	}
}
