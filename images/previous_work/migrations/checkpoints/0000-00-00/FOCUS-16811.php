<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

$dropdown_objects = Database::get("SELECT DISTINCT options_id FROM formbuilder_components WHERE type = 'dropdown' AND options_id IS NOT NULL");

foreach ($dropdown_objects as $object) {
	$object_id = $object['OPTIONS_ID'];
	$object    = Database::get("SELECT object FROM formbuilder_objects WHERE id = {$object_id}");
	$options   = json_decode($object[0]['OBJECT'], true);
	$changed   = false;
	$empty     = true;

	foreach ($options as $i => $option) {
		// If options already had sort, no changes needed
		if (isset($option['sort']) && !empty($option['sort']) && is_numeric($option['sort'])) {
			continue 2;
		}
	}

	// No existing sort for these options
	foreach ($options as $i => &$option) {
			$option['sort'] = $i;
	}
	unset($option);

	// Recompute hash since contents changed
	KSortRecursive($options, false);
	$object = json_encode($options);
	$hash   = sha1($object);

	$existingObject = Database::get(db_limit("SELECT id FROM formbuilder_objects WHERE hash = '{$hash}'", 1));
	if (!empty($existingObject) && $existingObject[0]['ID'] !== $object_id) {
		Database::query("UPDATE formbuilder_components SET options_id = {$existingObject[0]['ID']} WHERE options_id = {$object_id}");
		Database::query("DELETE FROM formbuilder_objects WHERE id = {$object_id}");
	} else {
		Database::query("UPDATE formbuilder_objects SET hash = '{$hash}', object = :object WHERE id = {$object_id}", compact('object'));
	}
}
