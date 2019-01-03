<?php
	//Required Log Columns
	$log_columns = [
		[
			//Title of the Immunization Field
			'title'      => 'Vaccination',
			//Type - Select
			'type'       => 'select',
			//Column Name
			'column'     => 'LOG_FIELD1',
			//Sort Order
			'sort_order' => 1,
		],
		[
			//Title of the Exemption Field
			'title'      => 'Exemption',
			//Type - Select
			'type'       => 'select',
			//Column Name
			'column'     => 'LOG_FIELD2',
			//Sort Order
			'sort_order' => 2,
		],
	];

	//Custom Field ID Query
	$query = "
		SELECT
			id
		FROM
			custom_fields
		WHERE
			alias = 'immunizations'
	";

	$exists = Database::get($query);

	if(empty($exists)) {
		//Query to determine if District has Immunization Custom Field
		$sql = "
			SELECT
				1
			FROM
				custom_fields
			WHERE
				legacy_id
			IN
				(400000929, 929)
		";
		//Existence of Immunization Custom Field
		$custom_field = Database::get($sql);
		//If Immunization Custom Field exists, run Update Query
		if(!empty($custom_field)) {
			//Query to make Immunization a System Field
			$sql = "
				UPDATE
					custom_fields
				SET
					system = 1,
					alias = 'immunizations'
				WHERE
					legacy_id
				IN
					(400000929, 929) AND
				NOT EXISTS(
					SELECT
						1
					FROM
						custom_fields
					WHERE
						legacy_id
					IN
						(400000929, 929) AND
					system = 1 AND
					alias = 'immunizations'
				)
			";
			//Runs the Query
			Database::query($sql);
		}
		//If Immunization Custom Field Doesnt Exists, create it as a System Field
		else {
			$custom_field = new CustomField();

			$custom_field
				->setSourceClass('SISStudent')
				->setType('log')
				->setTitle('Immunizations')
				->setAlias('immunizations')
				->setSystem(1)
				->persist();
		}
	}


	//Gets the Immunization Custom Field ID
	$field_id = current(Database::get($query)[0]);
	//Loop through each Log Column
	foreach($log_columns as $array) {
		//Sets Column Name
		$column     = $array['column'];
		//Sets Title
		$title      = $array['title'];
		//Sets Sort Order
		$sort_order = $array['sort_order'];
		//Sets Type
		$type       = $array['type'];
		//Check Log Column Existence
		$log        = "
			SELECT
				1
			FROM
				custom_field_log_columns
			WHERE
				field_id = {$field_id} AND
				column_name = '{$column}'
		";
		//Runs the Query
		$log_column = Database::get($log);
		//If the current Log Column Exists
		if(!empty($log_column)) {
			//Update Query to make Log Column a System Field
			$sql = "
				UPDATE
					custom_field_log_columns
				SET
					system = 1
				WHERE
					field_id = {$field_id} AND
					column_name = '{$column}' AND
				NOT EXISTS(
					SELECT
						1
					FROM
						custom_field_log_columns
					WHERE
						field_id = {$field_id} AND
						column_name = '{$column}' AND
						system = 1
				)
			";
			//Runs the Query
			Database::query($sql);
		}
		//If the current Log Column Doesn't Exist
		else {
			$log_column = new CustomFieldLogColumn();

			//Create the current Log Column as a System Field
			$log_column
				->setFieldId($field_id)
				->setColumnName($column)
				->setType($type)
				->setTitle($title)
				->setSortOrder($sort_order)
				->setSystem(1)
				->persist();
		}
	}