<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_pos_tax_accounting_strip")) {
	$sql =
		"CREATE TABLE gl_pos_tax_accounting_strip (
			id BIGINT PRIMARY KEY,
			deleted BIGINT,
			facility_id BIGINT,
			accounting_strip_id BIGINT,
			internal BIGINT,
			type CHAR
		)";

	Database::query($sql);

	$sql =
		"UPDATE
			gl_pos_tax_accounting_strip
		SET
			id = {{next:gl_maint_seq}}";
	$sql = Database::preprocess($sql);

	Database::query($sql);

	$oldSettings = [
		0 => [
			"S" => intval(Settings::get("state_tax_accounting_strip_id")),
			"L" => intval(Settings::get("local_tax_accounting_strip_id"))
		],
		1 => [
			"S" => intval(Settings::get("internal_state_tax_accounting_strip_id")),
			"L" => intval(Settings::get("internal_local_tax_accounting_strip_id"))
		]
	];

	foreach ($oldSettings as $internal => $data) {
		foreach ($data as $type => $stripId) {
			(new TaxAccountingStrip)
				->setInternal($internal)
				->setType($type)
				->setFacilityId(0)
				->setAccountingStripId($stripId)
				->persist();
		}
	}
}

Database::commit();
return true;
?>