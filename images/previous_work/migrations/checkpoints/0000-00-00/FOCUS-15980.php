<?php
	Migrations::depend('FOCUS-16510');

	// Make sure some primary keys exist
	$tables = [
		'custom_fields',
		'custom_field_categories',
	];

	foreach($tables as $table) {
		$cf_pkey = Database::getPrimaryKey($table);

		if(empty($cf_pkey)) {
			Database::query("ALTER TABLE {$table} ADD PRIMARY KEY (id)");
		}
	}

	$columns = [
		'address',
		'city',
		'state',
		'zipcode',
		'principal',
		'phone',
		'code',
	];

	SISSchool::dropViews();

	foreach($columns as $column) {
		if(Database::columnExists('schools', $column)) {
			Database::dropColumn('schools', $column);
		}
	}

	CustomFieldObject::upgradeCustomFields(['SISSchool']);

	$access = [
		'can_view',
		'can_edit',
	];

	$other_access = [
		'can_create',
		'can_delete',
	];

	$old_permission = 'School_Setup/Schools.php?new_school=true';
	$new_permission = 'School_Setup/AddSchool.php';

	foreach($access as $permission) {
		$sql = "
			UPDATE
				permission
			SET
				\"key\" = '{$new_permission}:{$permission}'
			WHERE
				\"key\" = '{$old_permission}:{$permission}' AND
				NOT EXISTS(
					SELECT
						1
					FROM
						permission
					WHERE
						\"key\" = '{$new_permission}:{$permission}'
				)
		";

		Database::query($sql);
	}

	$query = "
		WITH data_set AS (
			SELECT
				id,
				new_record,
				ROW_NUMBER() OVER (PARTITION BY source_class ORDER BY new_record ASC, id ASC) AS row_number
			FROM
				custom_fields
			WHERE
				source_class = 'SISSchool'
		),

		update_records AS (
			SELECT
				id,
				COALESCE(new_record, row_number) AS new_value
			FROM
				data_set
		)

		UPDATE
			custom_fields
		SET
			new_record = ur.new_value
		FROM
			update_records ur
		WHERE
			custom_fields.id = ur.id
			AND custom_fields.new_record IS NULL
	";

	Database::query($query);

	$query = "
		SELECT
			id
		FROM
			custom_fields
		WHERE
			source_class = 'SISSchool' AND type != 'log'
	";

	$sql = "
		SELECT
			id
		FROM
			custom_fields
		WHERE
			source_class = 'SISSchool' AND type = 'log'
	";

	$fields = array_column(Database::get($query), 'ID');
	$logs   = array_column(Database::get($sql), 'ID');
	$fields = array_merge($fields, [
		'schools|title',
		'schools|facility',
		'schools|min_syear',
		'schools|max_syear',
	]);

	$query = "
		SELECT
			profile_id AS ID, SUBSTRING(\"key\", {{postgres: CHAR_LENGTH}}{{mssql: LEN}}(\"key\") - 7, 8) AS ACCESS
		FROM
			permission p1
		WHERE
			\"key\" = 'School_Setup/Schools.php:can_view' OR \"key\" = 'School_Setup/Schools.php:can_edit'
	";

	$profiles = Database::get(Database::preprocess($query));

	foreach($profiles as $profile) {
		foreach($fields as $field_id) {
			$key = "SISSchool:{$field_id}:{$profile['ACCESS']}";
			$sql = "
				SELECT
					1
				FROM
					permission
				WHERE
					\"key\" = '{$key}' AND
					profile_id = {$profile['ID']}
			";

			$exists = Database::get($sql);

			if(empty($exists)) {
				$permission = new Permission();

				$permission
					->setProfileId($profile['ID'])
					->setKey($key)
					->persist();
			}
		}
	}

	foreach($logs as $field_id) {
		$query = "
			SELECT
				id
			FROM
				custom_field_log_columns
			WHERE
				field_id = {$field_id}
		";

		$log_columns = array_column(Database::get($query), 'ID');

		foreach($profiles as $profile) {
			foreach($log_columns as $log_id) {
				$key = "SISSchool:{$field_id}#{$log_id}:{$profile['ACCESS']}";
				$sql = "
					SELECT
						1
					FROM
						permission
					WHERE
						\"key\" = '{$key}' AND
						profile_id = {$profile['ID']}
				";

				$exists = Database::get($sql);

				if(empty($exists)) {
					$permission = new Permission();

					$permission
						->setProfileId($profile['ID'])
						->setKey($key)
						->persist();
				}
			}
		}
	}

	foreach($profiles as $profile) {
		if($profile['ACCESS'] === 'can_edit') {
			foreach($other_access as $other) {
				$profiles[] = [
					'ID'     => $profile['ID'],
					'ACCESS' => $other,
				];
			}
		}
	}

	foreach($profiles as $profile) {
		foreach($logs as $field_id) {
			$key = "SISSchool:{$field_id}:{$profile['ACCESS']}";
			$sql = "
				SELECT
					1
				FROM
					permission
				WHERE
					\"key\" = '{$key}' AND
					profile_id = {$profile['ID']}
			";

			$exists = Database::get($sql);

			if(empty($exists)) {
				$permission = new Permission();

				$permission
					->setProfileId($profile['ID'])
					->setKey($key)
					->persist();
			}
		}
	}

	$query = "
		UPDATE
			custom_fields
		SET
			max_length = NULL
		WHERE
			source_class = 'SISSchool' AND
			max_length = 0
	";

	Database::query($query);

	$fields = [
		'address' => [
			'title'       => 'Address',
			'column_name' => 'custom_200000319',
			'type'        => 'text',
		],
		'city' => [
			'title'       => 'City',
			'column_name' => 'custom_200000320',
			'type'        => 'text',
		],
		'state' => [
			'title'       => 'State',
			'column_name' => 'custom_200000321',
			'type'        => 'text',
		],
		'zipcode' => [
			'title'       => 'Zipcode',
			'column_name' => 'custom_200000322',
			'type'        => 'text',
		],
		'principal' => [
			'title'       => 'Principal',
			'column_name' => 'custom_200000324',
			'type'        => 'text',
		],
		'phone' => [
			'title'       => 'Phone',
			'column_name' => 'custom_200000323',
			'type'        => 'text',
		],
		'code' => [
			'title'       => 'School Number',
			'column_name' => 'custom_327',
			'type'        => 'text',
		],
	];

	// Charlotte has deleted the system fields and replaced them with their own
	if($GLOBALS['ClientId'] === 6142) {
		$fields['address']['column_name']   = 'custom_337';
		$fields['address']['title']         = 'School Address';
		$fields['city']['column_name']      = 'custom_339';
		$fields['city']['title']            = 'School City';
		$fields['state']['column_name']     = 'custom_342';
		$fields['state']['title']           = 'School State';
		$fields['zipcode']['column_name']   = 'custom_340';
		$fields['zipcode']['title']         = 'School Zip';
		$fields['principal']['column_name'] = 'custom_328';
		$fields['principal']['title']       = 'Principal';
		$fields['phone']['column_name']     = 'custom_330';
		$fields['phone']['title']           = 'Telephone Number';
	}

	foreach($fields as $alias => $definition) {
		$tmp_field = SISSchool::getFieldByColumnName($definition['column_name']);

		if(empty($tmp_field)) {
			$field = new CustomField();

			$field
				->setSourceClass('SISSchool')
				->setColumnName($definition['column_name'])
				->setTitle($definition['title']);
		}

		else {
			$field = new CustomField($tmp_field['id']);
		}

		$field
			->setAlias($alias)
			->setType($definition['type'])
			->setSystem(1)
			->persist();
	}

	SISSchool::refreshViews();
