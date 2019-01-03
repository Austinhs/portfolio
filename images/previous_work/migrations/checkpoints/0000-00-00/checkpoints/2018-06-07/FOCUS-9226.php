<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::sequenceExists('gl_user_preference_seq')) {	
	Database::createSequence('gl_user_preference_seq');
}

if(!Database::sequenceExists('gl_default_user_preference_seq')) {
	Database::createSequence('gl_default_user_preference_seq');
}

if(!Database::tableExists('gl_user_preferences')) {
	Database::query(Database::preprocess(
		"CREATE TABLE gl_user_preferences
		(
			id bigint PRIMARY KEY DEFAULT {{next:gl_user_preference_seq}},
			staff_id bigint,
			keyval varchar(255),
			value int
		)"));
}

if(!Database::tableExists('gl_default_user_preferences')) {
	Database::query(Database::preprocess(
		"CREATE TABLE gl_default_user_preferences
		(
			id bigint PRIMARY KEY DEFAULT {{next:gl_default_user_preference_seq}},
			last_changed_by_user bigint,
			keyval varchar(255),
			value int
		)"));
}

//Check for ESSInfoChgBatch, if it exists remove ESSInfoChgReq -- Because Batch replaced Req
$req = Database::get(db_limit(
	"SELECT 1
	FROM gl_permission
	WHERE type='approval'
	AND approval_type = 'ESSInfoChgReq'
", 1));

$batch = Database::get(db_limit(
	"SELECT 1
	FROM gl_permission
	WHERE type='approval'
	AND approval_type = 'ESSInfoChgBatch'
", 1));

if($batch && $req) {
	Database::query(
		"DELETE FROM gl_permission
		WHERE type='approval'
		AND approval_type = 'ESSInfoChgReq'
	");
}


$check_profiles = Database::get(db_limit(
	"SELECT 1
	FROM permission
	WHERE \"key\" = 'menu::gl_user_preference'
	",1));

if(empty($check_profiles)) {
	$profiles = Database::get(
		"SELECT
		profile_id
		FROM
		permission
		GROUP BY
		profile_id
	");
	
	$profiles = array_column($profiles, 'PROFILE_ID');
	
	$columns = [
		'profile_id',
		'key'
	];
	
	$records = [];
	
	foreach($profiles as $profile) {
		$records[] = [
			'profile_id' => $profile,
			'key' => 'menu::gl_user_preference'
		];
	}
	
	Database::insert('permission','permission_seq',$columns,$records);
}
