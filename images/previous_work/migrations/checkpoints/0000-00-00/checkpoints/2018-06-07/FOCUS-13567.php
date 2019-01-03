<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (Database::tableExists("gl_tx_element_crosswalk")) {
	if (!Database::columnExists("gl_tx_element_crosswalk", "peims_submission")) {
		Database::createColumn("gl_tx_element_crosswalk", "peims_submission", "VARCHAR", 255);

		$sql =
			"UPDATE
				gl_tx_element_crosswalk
			SET
				peims_submission = 'all'";

		Database::query($sql);
	}
}

if (Database::tableExists("gl_tx_strip_crosswalk")) {
	if (!Database::columnExists("gl_tx_strip_crosswalk", "peims_submission")) {
		Database::createColumn("gl_tx_strip_crosswalk", "peims_submission", "VARCHAR", 255);

		$sql =
			"UPDATE
				gl_tx_strip_crosswalk
			SET
				peims_submission = 'all'";

		Database::query($sql);
	}
}

Database::commit();
return true;
?>