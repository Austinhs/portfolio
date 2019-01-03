<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$lock_destination_project = Settings::get('lock_destination_project');

//checks if this migration has ran already
$project_transfer_migrated_already = Database::get(db_limit(
	"SELECT *
	FROM permission
	WHERE \"key\" = 'bm::allow_project_transfer'
", 1));

if(!$lock_destination_project && empty($project_transfer_migrated_already)) {
	$external_bm_profiles = Database::get("SELECT profile_id FROM permission WHERE \"key\" = 'menu::gl_budget_maintenance'");
	$internal_bm_profiles = Database::get("SELECT profile_id FROM permission WHERE \"key\" = 'menu::gl_ia_budget_maintenance'");

	$update_profiles = [];

	//Create Insert for External profiles
	foreach($external_bm_profiles as $profile) {
		$update_profiles[] = [
			'profile_id' => $profile['PROFILE_ID'],
			'key'        => 'bm::allow_project_transfer'
		];
	}

	//Create Insert for Internal Profiles -- Different key
	foreach($internal_bm_profiles as $profile) {
		$update_profiles[] = [
			'profile_id' => $profile['PROFILE_ID'],
			'key'        => 'bm::internal_allow_project_transfer'
		];
	}

	//Actually Insert new profile perms
	if($update_profiles) {
		Database::insert(
			'permission',
			'permission_seq',
			array_keys($update_profiles[0]),
			$update_profiles
		);
	}
	
}

Database::commit();
