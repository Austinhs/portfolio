<?php

require_once('./Warehouse.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if(Database::$type === 'mssql') {
	throw new Exception("This utility does not support SQL Server");
}

$default_suffix = date('Ymd');

if(!empty($_FILES)) {
	$suffix     = empty($_POST['suffix']) ? $default_suffix : $_POST['suffix'];
	$file       = file_get_contents($_FILES['standards']['tmp_name']);
	$data       = json_decode($file, true);
	$categories = $data['categories'];
	$standards  = $data['standards'];

	Database::begin();

	foreach($categories as $level => $tmp_categories) {
		if(empty($tmp_categories)) {
			continue;
		}

		$current_table = "standard_categories_{$level}";
		$current_seq   = "{$current_table}_seq";
		$table         = "{$current_table}_{$suffix}";

		// Drop the categories table if it exists
		if(Database::tableExists($table)) {
			Database::query("DROP TABLE {$table}");
		}

		// Create the categories table
		Database::query("SELECT * INTO {$table} FROM {$current_table} WHERE 0 = 1");

		// Add the new_id column
		Database::query("ALTER TABLE {$table} ADD new_id BIGINT NULL");

		// Add the new_parent_id column
		if(array_key_exists('PARENT_ID', $tmp_categories[0])) {
			Database::query("ALTER TABLE {$table} ADD new_parent_id BIGINT NULL");
		}

		// Insert the categories
		foreach($tmp_categories as $category) {
			$columns = array_keys($category);
			$params  = array_map(function($col) { return ":{$col}"; }, $columns);

			Database::query("
				INSERT INTO {$table} (
					" . join(', ', $columns) . "
				)
				VALUES (
					" . join(', ', $params) . "
				)
			", $category);
		}

		// Update the new_id column
		$sql = Database::preprocess("UPDATE {$table} SET new_id = {{next:{$current_seq}}}");

		Database::query($sql);

		// Update the new_parent_id column
		if(array_key_exists('PARENT_ID', $tmp_categories[0])) {
			$parent_level = $level - 1;
			$parent_table = "standard_categories_{$parent_level}_{$suffix}";

			Database::query("
				UPDATE
					{$table}
				SET
					new_parent_id = p.new_id
				FROM
					{$parent_table} p
				WHERE
					{$table}.parent_id IS NOT NULL AND
					p.id = {$table}.parent_id
			");
		}
	}

	// Restore standards
	$current_table = "standards";
	$current_seq   = "{$current_table}_seq";
	$table         = "{$current_table}_{$suffix}";

	// Drop the standards table if it exists
	if(Database::tableExists($table)) {
		Database::query("DROP TABLE {$table}");
	}

	// Create the standards table
	Database::query("SELECT * INTO {$table} FROM {$current_table} WHERE 0 = 1");

	// Add the new_id column
	Database::query("ALTER TABLE {$table} ADD new_id BIGINT NULL");

	// Add the new_category_*_id columns
	foreach($categories as $level => $tmp_categories) {
		if(empty($tmp_categories)) {
			continue;
		}

		Database::query("ALTER TABLE {$table} ADD new_category_{$level}_id BIGINT NULL");
	}

	// Insert the standards
	foreach($standards as $standard) {
		$columns = array_keys($standard);
		$params  = array_map(function($col) { return ":{$col}"; }, $columns);

		Database::query("
			INSERT INTO {$table} (
				" . join(', ', $columns) . "
			)
			VALUES (
				" . join(', ', $params) . "
			)
		", $standard);
	}

	// Update the new_id column
	$sql = Database::preprocess("UPDATE {$table} SET new_id = {{next:{$current_seq}}}");

	Database::query($sql);

	// Update the new_category_*_id columns
	foreach($categories as $level => $tmp_categories) {
		if(empty($tmp_categories)) {
			continue;
		}

		$category_table = "standard_categories_{$level}_{$suffix}";

		Database::query("
			UPDATE
				{$table}
			SET
				new_category_{$level}_id = c.new_id
			FROM
				{$category_table} c
			WHERE
				{$table}.category_{$level}_id IS NOT NULL AND
				c.id = {$table}.category_{$level}_id
		");
	}

	Database::commit();

	$standard_keys = array_keys(reset($standards));
	$standard_map  = array_combine($standard_keys, $standard_keys);

	// Map id -> new_id
	$standard_map['ID'] = 'NEW_ID';

	// Map category_*_id -> new_category_*_id
	foreach($categories as $level => $tmp_categories) {
		if(empty($tmp_categories)) {
			continue;
		}

		$standard_map["CATEGORY_{$level}_ID"] = "NEW_CATEGORY_{$level}_ID";
	}

	// Insert the standards
	$queries[] = "
INSERT INTO standards (
	" . join(', ', array_keys($standard_map)) . "
)
SELECT
	" . join(', ', array_values($standard_map)) . "
FROM
	standards_{$suffix};
	";

	// Drop the standards backup table
	$queries[] = "
DROP TABLE standards_{$suffix};
	";

	foreach($categories as $level => $tmp_categories) {
		if(empty($tmp_categories)) {
			continue;
		}

		$category_keys = array_keys(reset($tmp_categories));
		$category_map  = array_combine($category_keys, $category_keys);

		// Map id -> new_id
		$category_map['ID'] = 'NEW_ID';

		// Map parent_id -> new_parent_id
		if(array_key_exists('PARENT_ID', $category_map)) {
			$category_map['PARENT_ID'] = 'NEW_PARENT_ID';
		}

		// Insert the categories
		$queries[] = "
INSERT INTO standard_categories_{$level} (
	" . join(', ', array_keys($category_map)) . "
)
SELECT
	" . join(', ', array_values($category_map)) . "
FROM
	standard_categories_{$level}_{$suffix};
		";

		// Drop the categories backup table
		$queries[] = "
DROP TABLE standard_categories_{$level}_{$suffix};
		";
	}

	?>

	<p>To complete the process, please run the following queries:</p>
	<?= join('<br>', array_map(function($sql) { return "<pre>{$sql}</pre>"; }, $queries)); ?>

	<?php
}
else {
	?>

	<form enctype="multipart/form-data" method="POST">
		<p>
			<label>
				<span>Table Suffix:</span>
				<input type="text" name="suffix" value="<?= $default_suffix ?>">
			</label>
		</p>
		<p>
			<input type="file" name="standards"><br>
		</p>
		<p>
			<button type="submit">Import</button>
		</p>
	</form>

	<?php
}
