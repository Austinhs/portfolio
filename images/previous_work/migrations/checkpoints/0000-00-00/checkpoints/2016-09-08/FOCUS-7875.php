<?php

// Migrate regular form permissions
$insert_objects = [];

foreach([ 'SISStudent', 'SISUser' ] as $class) {
	$categories = $class::getCategories();

	$existing_old = "
		SELECT
			profile_id,
			\"key\"
		FROM
			" . Permission::$table . "
		WHERE
			\"key\" LIKE '{$class}:%:can_%'
	";

	$existing_new = "
		SELECT
			profile_id,
			\"key\"
		FROM
			" . Permission::$table . "
		WHERE
			\"key\" LIKE '{$class}:form:%:can_create' OR
			\"key\" LIKE '{$class}:form:%:can_delete'
	";

	$old_rows  = Database::get($existing_old);
	$old_index = Database::reindex($old_rows, [ 'key', 'profile_id' ]);
	$new_rows  = Database::get($existing_new);
	$new_index = Database::reindex($new_rows, [ 'key', 'profile_id' ]);

	foreach($categories as $category_id => $category) {
		if(!$class::isForm($category_id)) {
			continue;
		}

		// Get all the fields for this category
		$fields = $class::getFields($category_id);

		if(empty($fields)) {
			continue;
		}

		// Get all the profiles that have access to
		// edit at least one field in this category
		$profile_ids = [];

		foreach($fields as $field_id => $field) {
			$edit_key = "{$class}:{$field_id}:can_edit";

			if(isset($old_index[$edit_key])) {
				foreach(array_keys($old_index[$edit_key]) as $profile_id) {
					$profile_ids[$profile_id] = true;
				}
			}
		}

		// Permission keys for this category
		$create_key = "{$class}:form:{$category_id}:can_create";
		$delete_key = "{$class}:form:{$category_id}:can_delete";

		foreach($profile_ids as $profile_id => $flag) {
			if(empty($new_index[$create_key][$profile_id])) {
				$create = new Permission();

				$create
					->setKey($create_key)
					->setProfileId($profile_id);

				$insert_objects[] = $create;
			}

			if(empty($new_index[$delete_key][$profile_id])) {
				$delete = new Permission();

				$delete
					->setKey($delete_key)
					->setProfileId($profile_id);

				$insert_objects[] = $delete;
			}
		}
	}
}

Permission::insert($insert_objects);
