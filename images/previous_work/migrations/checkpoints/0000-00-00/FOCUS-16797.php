<?php

// Tags: Formbuilder
$object_cache = [];

$forms = array_column(Database::get("SELECT id FROM formbuilder_forms WHERE deleted_at IS NULL"), 'ID');
foreach ($forms as $form_id) {
	$pages = Database::get("
		SELECT c.id, c.layout_id
		FROM formbuilder_components c
		WHERE c.form_id = {$form_id}
		  AND c.type = 'page-container'
		  AND c.removed_revision IS NULL
		ORDER BY (SELECT o.object FROM formbuilder_objects o WHERE o.id = c.layout_id) ASC
	");

	foreach ($pages as $index => $page) {
		$component_id = $page['ID'];
		if (isset($object_cache[$index])) {
			$object_id = $object_cache[$index];
		} else {
			$results = Database::get("SELECT id FROM formbuilder_objects WHERE object = '{$index}'");
			if (empty($results)) {
				throw new \Exception("[FOCUS-16797] Expected object with value {$index}, but database does not have that object.");
			}

			$object_id = $object_cache[$index] = $results[0]['ID'];
		}

		if ($page['LAYOUT_ID'] !== $object_id) {
			Database::query("UPDATE formbuilder_components SET layout_id = {$object_id} WHERE id = {$component_id}");
		}
	}
}
