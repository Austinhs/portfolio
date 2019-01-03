<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$setting = (new Setting)
	->setRecord([
		"key"   => "ia_budget_revision_classification",
		"value" => "collected"
	]);

// Do not want automation to commit sudoku, so only running for Monroe as they - and they alone - requested this immediately.
if ((int) $GLOBALS["ClientId"] === 6140005738) {
	$setting->setValue("budgeted");

	$sql =
		"UPDATE
			gl_journals
		SET
			budgeted = collected,
			collected = 0
		WHERE
			source = 'BM Internal Transfer' AND
			COALESCE(collected, 0) != 0 AND
			COALESCE(budgeted, 0) = 0";

	Database::query($sql);
}

$setting->persist();
Database::commit();
return true;
?>