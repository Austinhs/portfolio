<?php

Migrations::depend('FOCUS-8295a');
Migrations::depend('FOCUS-8295b');

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$form_ids = Database::get("SELECT instance_id FROM gl_permission WHERE approval_type = 'FormRequest' AND instance_id IS NOT NULL");
$form_ids = array_column($form_ids, 'INSTANCE_ID');
foreach ($form_ids as $form_id) {
	$fields = array_keys(FormRequestApprovalFlow::getFields($form_id));
	if (!empty($fields)) {
		// Approval Flow isn't working correctly if value != '*' so just delete regardless...
		$fields = "'" . implode("','", array_map('DBEscapeString', $fields)) . "'";
		$fields = "field NOT IN ({$fields}) AND";
	} else {
		$fields = "";
	}

	Database::query("
		DELETE FROM gl_permission_value
		WHERE {$fields} permission_id IN (
			SELECT id
			FROM gl_permission
			WHERE approval_type = 'FormRequest'
			  AND instance_id = {$form_id}
		)
	");
}

Database::commit();
