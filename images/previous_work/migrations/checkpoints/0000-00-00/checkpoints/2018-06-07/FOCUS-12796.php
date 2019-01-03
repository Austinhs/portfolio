<?php
// Tags: Formbuilder
Database::isolate(function() {
	$start_time = Database::get(db_limit("
		SELECT start_time
		FROM updater_log
		WHERE svn_url = 'svn://focus-sis.com/focus/tags/8.6.0'
		ORDER BY start_time ASC
	", 1));

	// If they didn't use the official updater, they're on their own
	if (empty($start_time)) {
		return true;
	}

	$start_time     = date('Y-m-d H:i:s', strtotime($start_time[0]['START_TIME']));
	$form_instances = Database::get("
		SELECT id, form_id
		FROM sss_form_instances
		WHERE raw_data IS NOT NULL
		  AND (
		  	(saved IS NOT NULL AND saved > '{$start_time}') OR
		  	(drafted IS NOT NULL AND drafted > '{$start_time}')
		  )
	");

	foreach ($form_instances as $data) {
		$form_id          = $data['FORM_ID'];
		$form_instance_id = $data['ID'];

		// We query these in loop to use minimal RAM, primary key is fast query
		$saved_layout     = Database::get("SELECT raw_data FROM sss_form_instances WHERE id = {$form_instance_id}");
		$original_layout  = Database::get("SELECT layout FROM sss_forms WHERE id = {$form_id}");

		if (empty($original_layout)) {
			throw new \Exception("The form {$form_id} does not exist, found on form instance {$form_instance_id}.");
		}

		$saved_layout    = json_decode($saved_layout[0]['RAW_DATA'], true);
		$original_layout = json_decode($original_layout[0]['LAYOUT'], true);

		foreach ($saved_layout['components'] as $component_id => &$component) {
			if (key_exists('enabled', $component) && $component['enabled'] === false) {
				if (key_exists($component_id, $original_layout['components'])) {
					$component['enabled'] = $original_layout[$component_id]['enabled'];
				} else if (strpos($component['generalCode'], 'this.setEnabled') !== false) {
					$component['enabled'] = true;
				}
			}
		}
		unset($component, $original_layout);

		$query  = "UPDATE sss_form_instances SET raw_data = :raw_data WHERE id = {$form_instance_id}";
		$params = [
			'raw_data' => json_encode($saved_layout)
		];

		Database::query($query, $params);
		unset($params, $query, $saved_layout);
	}
});
