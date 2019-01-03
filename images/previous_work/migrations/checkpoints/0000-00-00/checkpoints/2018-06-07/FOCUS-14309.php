<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

//Data for custom fields - adding to W-4 Information category
$w4_cat = Database::get("SELECT ID FROM CUSTOM_FIELD_CATEGORIES WHERE TITLE = 'W4 Information'");
if($w4_cat) {
	$w4_cat_id = $w4_cat[0]['ID'];
	$w4_fields = [
	  [
			'title' => 'IRS Lockout',
			'column' => 'irs_lockout_ph',
			'type' => 'holder'
		],
		[
			'title' => 'IRS Lock Start',
			'column' => 'irs_lock_start',
			'type' => 'date'
		],
	  [
			'title' => 'IRS Lock End',
			'column' => 'irs_lock_end',
			'type' => 'date'
		]
	];

	$access_profiles = Database::get("SELECT PROFILE_ID FROM PERMISSION WHERE \"key\" = 'hr::demographic'");

	$sort_increment = 0;
	foreach ($w4_fields as $field) {
		if (empty(FocusUser::getFieldByAlias($field['column']))) {
			//Add field
			$cf = new CustomField();
			$cf->setTitle($field['title']);
			$cf->setAlias($field['column']);
			$cf->setType($field['type']);
			$cf->setSourceClass('FocusUser');
			$cf->setColumnName($field['column']);
			$cf->setSystem(1);
			$cf->persist();

			//Get Field info
			$field_id = $cf->getId();

			foreach ($access_profiles as $access_profile) {
				Database::query("INSERT INTO PERMISSION (PROFILE_ID, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'FocusUser:{$field_id}:can_view')");
				Database::query("INSERT INTO PERMISSION (PROFILE_ID, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'FocusUser:{$field_id}:can_edit')");
			}

	    //Join to category - add to the bottom
			$cfjc = new CustomFieldJoinCategory();
			$cfjc->setCategoryId($w4_cat_id);
			$cfjc->setFieldId($field_id);
			$cfjc->setSortOrder(8675309+$sort_increment);
			$cfjc->persist();

	  	$sort_increment++;

		}
	}

	$cfjc = new CustomFieldJoinCategory();
	$cfjc->fixSortOrders();
}
else {
	return false;
}

Database::commit();
