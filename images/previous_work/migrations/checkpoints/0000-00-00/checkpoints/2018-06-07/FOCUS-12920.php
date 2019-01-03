<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$exists = Database::tableExists("gl_accounting_action");

if (!$exists) {
	$sql =
		"CREATE TABLE gl_accounting_action (
			id BIGINT PRIMARY KEY,
			deleted INT,
			action VARCHAR(255),
			credit_account_id BIGINT,
			debit_account_id BIGINT,
			funds TEXT,
			fallback INT
		)";

	Database::query($sql);

	$sql =
		"UPDATE
			gl_accounting_action
		SET
			id = {{next:gl_maint_seq}}";
	$sql = Database::preprocess($sql);

	Database::query($sql);
}

$keys         = [
	"initial_e_a",
	"initial_e_b",
	"initial_r_a",
	"initial_r_b",
	"revise_e_a",
	"revise_e_b",
	"revise_r_a",
	"revise_r_b",
	"amend_e_a",
	"amend_e_b",
	"amend_r_a",
	"amend_r_b"
];
$sql          =
	"SELECT
		\"key\",
		value
	FROM
		gl_setting
	WHERE
		\"key\" LIKE 'initial_e%' OR
		\"key\" LIKE 'initial_r%' OR
		\"key\" LIKE 'revise_e%' OR
		\"key\" LIKE 'revise_r%' OR
		\"key\" LIKE 'amend_e%' OR
		\"key\" LIKE 'amend_r%'";
$res          = Database::reindex(Database::get($sql), ["KEY"]);
$actions      = [];
$missing_keys = array_diff_key(array_flip($keys), $res);
$res         += array_fill_keys(
	array_flip($missing_keys),
	[
		"VALUE" => null
	]
);

foreach ($res as $key => $settings) {
	foreach ($settings as $data) {
		$value  = $data["VALUE"];
		$action = strtolower(substr($key, 0, -2));
		$type   = strtolower(substr($key, -1));
		$prefix = ($type === "a") ? "debit" : "credit";

		if (!isset($actions[$action])) {
			$actions[$action] = [
				"action"   => $action,
				"fallback" => 1
			];
		}

		$actions[$action]["{$prefix}_account_id"] = $value;
	}
}


$keys = array_keys($actions);
$keys = "'".implode("','",$keys)."'";
$sql  = 
	"SELECT
		*
	FROM
		gl_accounting_action
	WHERE
		action in ({$keys}) AND
		fallback = 1
";

$test = Database::get($sql);

foreach ($test as $record){
	$action = $record['ACTION'];
	unset($actions[$action]);
};

$actions = array_values($actions);

if ($actions) {
	Database::insert("gl_accounting_action", "gl_maint_seq", array_keys($actions[0]), $actions);
}

Database::commit();
return true;
?>