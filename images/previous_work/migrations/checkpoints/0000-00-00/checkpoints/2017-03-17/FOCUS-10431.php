<?php
// Tags: Formbuilder
// This migration only needs to be run for sites that had an old version of SSS code
if (!Database::tableExists('sss_form_field_instances')) {
	return true;
}

set_time_limit(0);
ini_set('memory_limit', '16G');

$dropdowns = Database::get("
	SELECT ff.type, ff.options, ffi.id, ffi.value
	FROM sss_form_field_instances ffi
	INNER join sss_form_fields ff ON ff.id = ffi.field_id AND ff.id = ffi.data_id
	WHERE ff.type = 'dropdown'
	  AND ffi.value LIKE '[%]'
	  AND ffi.value NOT like '%{%'
	  AND ffi.value IS NOT NULL
");

foreach ($dropdowns as $dropdown) {
	$id        = $dropdown['ID'];
	$options   = json_decode($dropdown['OPTIONS'], true);
	$old_value = json_decode($dropdown['VALUE'], true);

	$new_value = array_map(function($value) use ($options) {
		// First try to match ID
		foreach ($options as $option) {
			if (isset($option['id']) && $option['id'] === $value) {
				return [
					'text'  => $option['text'],
					'value' => $option['value']
				];
			}
		}

		// If no ID, try value
		foreach ($options as $option) {
			if (isset($option['value']) && $option['value'] == $value) {
				return [
					'text'  => $option['text'],
					'value' => $option['value']
				];
			}
		}

		// Last resort use the index
		if (!isset($options[$value])) {
			throw new Exception("Option not found");
		} else {
			return $options[$value];
		}
	}, $old_value);

	$query  = "UPDATE sss_form_field_instances SET value = :value WHERE id = :id";
	$params = [
		'id'    => $id,
		'value' => json_encode($new_value)
	];

	Database::get($query, $params);
}
