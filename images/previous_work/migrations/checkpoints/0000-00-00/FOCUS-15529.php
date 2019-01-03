<?php

Migrations::depend("FOCUS-14076");
Migrations::depend("FOCUS-14874");

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

//Data for fields
$demo_fields = [
	[
		'title' => 'Benefits Cancellation Date',
		'column' => 'benefits_cancellation_date',
		'type' => 'date'
	]
];

//Add Categories
$erp_cat = CustomFieldCategory::getOneAndLoad("title = 'Employee Demographic'");
$erp_cat_id = $erp_cat->getId();

$access_profiles = Database::get("SELECT PROFILE_ID FROM PERMISSION WHERE \"key\" = 'menu::employee'");

$sort_increment = 0;
foreach ($demo_fields as $field) {
	if (empty(FocusUser::getFieldByAlias($field['column']))) {
		//Add field
		$cf = new CustomField();
		$cf->setTitle($field['title']);
		$cf->setAlias($field['column']);
		$cf->setType($field['type']);
		$cf->setSourceClass('FocusUser');
		$cf->setSystem(1);
		$cf->persist();

		//Get Field info
		$field_id = $cf->getId();
		$field_column = $cf->getColumnName();

		foreach ($access_profiles as $access_profile) {
			Database::query("INSERT INTO PERMISSION (PROFILE_ID, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'FocusUser:{$field_id}:can_view')");
			Database::query("INSERT INTO PERMISSION (PROFILE_ID, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'FocusUser:{$field_id}:can_edit')");
		}

		//Join to category
		$cfjc = new CustomFieldJoinCategory();
		$cfjc->setCategoryId($erp_cat_id);
		$cfjc->setFieldId($field_id);
		$cfjc->setSortOrder(8675309+$sort_increment);
		$cfjc->persist();

		$sort_increment++;

		$lookup_key = $field['column'];

		//Add Select Options & Update Users
		if (Database::tableExists('gl_hr_demographic_old') && Database::columnExists('gl_hr_demographic_old', $field['column'])) {
			Database::query(
				"UPDATE USERS
				SET {$field_column} = GL_HR_DEMOGRAPHIC_OLD.{$field['column']}
				FROM GL_HR_DEMOGRAPHIC_OLD
				WHERE GL_HR_DEMOGRAPHIC_OLD.STAFF_ID = USERS.STAFF_ID AND GL_HR_DEMOGRAPHIC_OLD.{$field['column']} IS NOT NULL"
			);
		}
	}
}

$cfjc = new CustomFieldJoinCategory();
$cfjc->fixSortOrders();

Database::commit();