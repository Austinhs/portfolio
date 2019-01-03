<?php

if(!Database::columnExists('edit_rules', 'alert_icons')) {
	Database::createColumn('edit_rules', 'alert_icons', 'varchar');
}

if(!Database::columnExists('edit_rules', 'type')) {
	Database::createColumn('edit_rules', 'type', 'varchar');
	Database::query("UPDATE edit_rules SET type='validation';");
	Database::query("UPDATE edit_rules SET type='alert' WHERE alert_icons IS NOT NULL;");
}



global $uploaded_assets_dir, $staticpath;

$alert_icons_dir = "{$uploaded_assets_dir}/alert_icons";

if(!is_dir($alert_icons_dir)) {
	mkdir($alert_icons_dir);
}

$alert_icons_dir = realpath($alert_icons_dir);
$triggers        = Database::get("select * from letter_triggers where abs(trigger_event) = 10;");

foreach ($triggers as $key => $value) {
	$template_id    = unserialize($value['TEMPLATE_ID']);
	$event_options  = unserialize($value['EVENT_OPTIONS']);
	$action_options = unserialize($value['ACTION_OPTIONS']);
	$field_id       = empty($event_options['FIELD']) ? null : $event_options['FIELD'];
	$field          = SISStudent::getFieldByColumnName('CUSTOM_'.$event_options['FIELD']);
	$name           = $event_options['ALERT']."-".$field.": ".$event_options['FILTER'];
	$rule_exists    = Database::get("select 1 from EDIT_RULES where name = '{$name}'");

	if(SISStudent::isFormField($field['id']) == false && !empty($field) && empty($rule_exists) && $field_id != 'N/A') {
		$enabled          = (intval($value['EVENT_TRIGGER']) > 0) ? 1 : null;
		$filename         = empty($event_options['FILE']) ? null : $event_options['FILE'];
		$source           = "{$uploaded_assets_dir}/{$filename}";
		$destination      = "{$alert_icons_dir}/{$filename}";
		$source_path      = realpath($source);
		$destination_path = realpath($destination);

		//import the previous alert image if it doesn't exist
		if(empty($destination_path)) {
			$created  = !empty($source_path) && copy($source, $destination);
			$filename = $created ? $filename : null;
		}

		//prepare alert_icons field for edit_rule
		$alert = [
			"image_name"     => $filename,
			"tool_tip"       => empty($event_options['ALERT']) ? null : $event_options['ALERT'],
			"tool_tip_field" => "",
			"icon_source"    => "User_uploaded"
		];

		$alert = json_encode($alert);

		//create and save the edit rule
		$rule = new EditRule();

		$rule
			->setName($name)
			->setCategory('SISStudent')
			->setAlertIcons($alert)
			->setPreventsSaving(1)
			->setEnabled($enabled)
			->setType('alert')
			->persist();

		$val = '';

		//retrieve select values for fields of select type
		if($field['type'] == 'select') {
			$options = SISStudent::getSelectOptions($field['id']);
			foreach ($options as $k => $v) {
				if($v['text'] == $event_options['FILTER']) {
					$val = '["'.$v['value'].'"]';
				}
			}
		} else {
			$val = $event_options['FILTER'];
		}

		//save the criterion to the above rule
		$criterion = new EditRuleCriterion();

		$criterion
			->setField1($field['id'])
			->setType($field['type'])
			->setValue($val)
			->setRule_id($rule->getId())
			->persist();
	}
}

