<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$has_value = Database::columnExists('permission', 'value');

$where = [
	"\"key\" LIKE '%gl_settings%'"
];

if($has_value) {
	$where[] = "value = 1";
}

$query = "
	SELECT
		profile_id
	FROM
		PERMISSION
	WHERE
		((" . join(') AND (', $where) . "))
";

$tabs = array(
	"fiscal-years"        => 'Fiscal Years',
	"fiscal-months"       => 'Fiscal Months',
	"accounts"            => 'Accounts',
	"element-categories"  => 'Element Categories',
	"elements"            => 'Elements',
	"accounting-strips"   => 'Accounting Strips',
	"facilities"          => 'Facilities',
	"objects"             => 'Objects',
	"misc"                => 'Miscellaneous',
	"accounts-payable"    => 'Accounts Payable',
	"accounts-receivable" => 'Accounts Receivable',
	"payroll"             => 'Payroll',
	"fixed-assets"        => 'Fixed Assets',
	"manual-journals"     => 'Manual Journals',
	"internal-accounts"   => 'Internal Accounts',
	"signatures"          => 'Signatures',
	"1098T"               => '1098-T'
);

$results = Database::get($query);

foreach($results as $result) {
	$profile_id = $result['PROFILE_ID'];

	foreach($tabs as $tab_key => $tab_name) {
		$where = array(
			"\"key\" = 'setting::setup-{$tab_key}'",
			"profile_id = {$profile_id}"
		);

		$tmp_permission = Permission::getOneAndLoad($where);

		if(!empty($tmp_permission)) {
			continue;
		}

		$permission = (new Permission)
			->setKey("setting::setup-{$tab_key}")
			->setProfileId($profile_id);

		if($has_value) {
			$permission->setValue(1);
		}

		$permission->persist();
	}
}
?>
