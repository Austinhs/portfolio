<?php
/**
 * This migration tries to replace bad dates on logging fields
 * covering all possible cases of logging fields of type date
 * Example: '0016-01-01' => '2016-01-01'
 * This could be a very long running process
 */

set_time_limit(0);
ini_set('memory_limit','-1');

$type = [
	'SISStudent' => [
		'table' => 'students',
		'form_record_table' => 'students_form_records'
	],
	'SISUser' => [
		'table' => 'users',
		'form_record_table' => 'users_form_records'
	]
];

foreach ($type as $key => $value) {
	$class             = $key;
	$table             = $value['table'];
	$form_record_table = $value['form_record_table'];

	$sql = "
		SELECT DISTINCT
			(
				CASE WHEN cflc.id IS NULL
				THEN (
					CASE WHEN COALESCE(cfc.form, 0) != 0
					THEN '{$form_record_table}'
					ELSE '{$table}'
					END
				)
				ELSE 'custom_field_log_entries'
				END
			) AS table_name,
			COALESCE(cflc.column_name, cf.column_name) AS column_name,
			cflc.field_id
		FROM
			custom_fields cf LEFT JOIN
			custom_fields_join_categories cfjc ON
				cfjc.field_id = cf.id LEFT JOIN
			custom_field_categories cfc ON
				cfc.id = cfjc.category_id LEFT JOIN
			custom_field_log_columns cflc ON
				cflc.field_id = cf.id
		WHERE
			COALESCE(cf.deleted, cfjc.deleted, cfc.deleted, cflc.deleted, 0) = 0 AND
			(
				cf.source_class = '{$class}' AND
				cf.type = 'date' OR (
					cf.type = 'log' AND
					cflc.type = 'date'
				)
			);
		";

	$results = Database::get($sql);

	foreach ($results as $result) {
		$table_name  = $result['TABLE_NAME'];
		$column_name = $result['COLUMN_NAME'];
		$field_id    = $result['FIELD_ID'];

		$extra_where = "";
		if(!empty($field_id)){
			$extra_where = "AND field_id = {$field_id}";
		}

		if(Database::columnExists("{$table_name}", "{$column_name}")){
			$query = "
				SELECT COUNT({$column_name}) AS C
				FROM {$table_name}
				WHERE CAST({$column_name} AS varchar) LIKE '0016%' {$extra_where}
			";
			$result = Database::get($query);

			//Do a checking first since update operation take much more time
			if($result[0]['C'] > 0){

				$data_type = Database::getColumnType($table_name, $column_name);

				if(!empty($data_type)){

					$query = "
						UPDATE
							{$table_name}
						SET
							{$column_name} = CAST(REPLACE(CAST({$column_name} AS varchar), '00', '20') AS {$data_type})
						WHERE
							CAST({$column_name} AS varchar) LIKE '0016%'
							{$extra_where}
					";

					Database::query($query);
				}
			}
		}
	}
}