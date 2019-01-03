<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::tableExists('gl_pr_history_run_wages')) {
	return false;
}

Database::begin();

if (!Database::columnExists('gl_pr_history_run_wages', 'affects_contract'))
{
	Database::createColumn('gl_pr_history_run_wages', 'affects_contract', 'int');

	Database::query("update gl_pr_history_run_wages set affects_contract = 0");

	Database::query("
		update gl_pr_history_run_wages set affects_contract = 1
		WHERE    adjustment_id IS NULL
		AND      supplement_id IS NULL
		AND      wage_type in (".WageTypes::contract_wages().")
	");

	Database::query("
		update gl_pr_history_run_wages set affects_contract = 2
		WHERE  adjustment_id IS NOT NULL
			and exists (
				select ''
				from gl_pr_run_control_adjustments adj
				join gl_pr_adjustment_codes c ON c.id = adj.adjustment_code_id
				where (c.applies_to_contract IS NULL OR c.applies_to_contract = 'Y')
				and c.id = gl_pr_history_run_wages.adjustment_id
				and adj.adjustment_code_id = gl_pr_history_run_wages.adjustment_id
			)
		");

}
Database::commit();

?>
