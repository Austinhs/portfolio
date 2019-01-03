<?php

require_once('../Warehouse.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if(User('USERNAME') !== 'focus') {
	echo "You are not allowed to use this program.";
	exit;
}

if(!empty($_REQUEST['backup'])) {
	foreach($_REQUEST['backup'] as $class => $categories) {
		foreach($categories as $table) {
			if(!Database::tableExists($table)) {
				echo "Table {$table} does not exist. Please restore the tables first.";
				exit;
			}
		}
	}

	Database::begin();
}

// Generate some SQL to check for bad data
$classes = [
	'SISStudent',
	'FocusUser'
];

$bad_data = [];

foreach($classes as $class) {
	$form_table = $class::getFormTable();
	$fix_table  = "___{$form_table}_fix_sm";
	$insert     = [];
	$update     = false;

	$generate_sql = "
		SELECT
			cf.column_name,
			cf.id AS field_id,
			cfc.id AS category_id,
			cfc.legacy_id,

			CONCAT('
				SELECT
					source_id
				FROM
					{$form_table}
				WHERE
					category_id = ', cfc.id, ' AND
					', cf.column_name, ' IS NOT NULL
				GROUP BY
					source_id
				HAVING
					COUNT(1) > 1
			') AS sql
		FROM
			custom_fields cf JOIN
			custom_fields_join_categories cfjc ON
				cfjc.field_id = cf.id JOIN
			custom_field_categories cfc ON
				cfc.id = cfjc.category_id
		WHERE
			COALESCE(cf.deleted, cfc.deleted, cfjc.deleted, 0) = 0 AND
			cfc.form = 1 AND
			cf.type = 'multiple' AND
			cf.source_class = :class
	";

	$generate_params = [
		'class' => $class
	];

	$sql_rows = Database::get($generate_sql, $generate_params);

	foreach($sql_rows as $row) {
		$sql         = $row['SQL'];
		$column      = $row['COLUMN_NAME'];
		$field_id    = $row['FIELD_ID'];
		$category_id = $row['CATEGORY_ID'];
		$legacy_id   = $row['LEGACY_ID'];

		if(empty($_REQUEST['backup'])) {
			$sql  = db_limit($sql, 1);
			$rows = Database::get($sql);

			if(!empty($rows)) {
				$bad_data[$class][$category_id] = $legacy_id;
			}
		}
		else {
			$source_ids = array_column(Database::get($sql), 'SOURCE_ID');

			if(empty($source_ids)) {
				continue;
			}

			$backup_tables = $_REQUEST['backup'][$class];

			if(empty($backup_tables[$category_id])) {
				echo "No table for category {$category_id} (legacy: {$legacy_id})";
				exit;
			}

			$backup_table = $backup_tables[$category_id];

			if(empty($backup_table) || !Database::columnExists($backup_table, $column)) {
				echo "Wrong table for column {$column}.";
				exit;
			}

			$option_sql = "
				SELECT
					cfso.id,
					cfso.label
				FROM
					" . CustomFieldSelectOption::$table . " cfso
				WHERE
					cfso.source_class = 'CustomField' AND
					cfso.source_id = :field_id
			";

			$option_params = [
				'field_id' => intval($field_id)
			];

			$options   = Database::get($option_sql, $option_params);
			$options_i = array_map('reset', Database::reindex($options, 'label'));

			foreach($source_ids as $source_id) {
				$current_sql = "
					SELECT
						current.id,
						current.{$column} AS bad_data,
						backup.{$column} AS good_data
					FROM
						{$form_table} current JOIN
						{$backup_table} backup ON
							backup.id = current.legacy_id
					WHERE
						current.source_id = :source_id AND
						current.category_id = :category_id
				";

				$current_params = [
					'source_id'   => intval($source_id),
					'category_id' => intval($category_id)
				];

				$current_rows = Database::get($current_sql, $current_params);

				foreach($current_rows as $row) {
					$id         = intval($row['ID']);
					$correct    = [];
					$incorrect  = $row['BAD_DATA'];
					$labels_str = $row['GOOD_DATA'] ?: '';
					$labels     = array_filter(explode('||', $labels_str));

					foreach($labels as $label) {
						if(isset($options_i[$label])) {
							$correct[] = strval($options_i[$label]['ID']);
						}
					}

					$correct = empty($correct) ? null : json_encode($correct);

					if((empty($correct) && empty($incorrect)) || $correct === $incorrect) {
						continue;
					}

					$insert[$id] = [
						'column_name' => $column,
						'correct'     => $correct,
					];

					if(count($insert) > 2000) {
						insertRecords($form_table, $fix_table, $insert);

						$update = true;
						$insert = [];
					}
				}
			}
		}
	}

	if(!empty($insert)) {
		insertRecords($form_table, $fix_table, $insert);

		$update = true;
		$insert = [];
	}

	if($update) {
		// First get all the columns we need to update
		$columns_sql = "
			SELECT DISTINCT
				column_name
			FROM
				{$fix_table}
		";

		$columns = array_column(Database::get($columns_sql), 'COLUMN_NAME');

		foreach($columns as $column) {
			$sql = "
				UPDATE
					{$form_table}
				SET
					{$column} = fix.correct
				FROM
					{$fix_table} fix
				WHERE
					fix.column_name = :column AND
					fix.record_id = {$form_table}.id
			";

			$params = [
				'column' => $column
			];

			Database::query($sql, $params);
		}

		Database::query("
			DROP TABLE {$fix_table}
		");
	}
}

if(!empty($_REQUEST['backup'])) {
	Database::commit();
	echo "Finished correcting data.";
	exit;
}

function insertRecords($table, $fix_table, $records) {
	if(empty($records)) {
		return;
	}

	$text_type = Database::$type === 'mssql' ? 'VARCHAR(MAX)' : 'TEXT';

	if(!Database::tableExists($fix_table)) {
		Database::query("
			CREATE TABLE {$fix_table} (
				id BIGINT PRIMARY KEY,
				record_id BIGINT NOT NULL,
				column_name {$text_type} NOT NULL,
				correct {$text_type} NULL
			)
		");
	}

	if(!Database::sequenceExists("{$fix_table}_seq")) {
		Database::createSequence("{$fix_table}_seq");
	}

	$rows = [];

	foreach($records as $id => $record) {
		$rows[] = [
			'record_id'   => $id,
			'column_name' => $record['column_name'],
			'correct'     => $record['correct'],
		];
	}

	Database::insert(
		$fix_table,
		"{$fix_table}_seq",
		[ 'record_id', 'column_name', 'correct' ],
		$rows
	);
}

if(empty($_REQUEST['backup'])) {
	if(!empty($bad_data)) {
		$tables = [];

		$legacy_prefix = [
			'SISStudent' => 'student_form_records_',
			'SISUser'    => 'user_form_records_',
		];

		foreach($bad_data as $class => $categories) {
			$prefix = $legacy_prefix[$class];

			foreach($categories as $category_id => $legacy_id) {
				$tables[] = <<<HTML
					<div>
						<label><strong>{$prefix}{$legacy_id}</strong><br><input style="width: 300px" type="text" name="backup[{$class}][{$category_id}]"></label>
					</div>
HTML;
			}
		}

		$tables = join('', $tables);

		echo <<<HTML
			<form method="POST">
				<h1>Restore Data from select multiple fields inside form categories</h1>
				<h2>Please enter the names of the backup tables</h2>
				{$tables}<br>
				<button type="submit">Restore Data</button>
			</form>
HTML;
	}
	else {
		echo "You do not need to run this utility.";
	}
}
