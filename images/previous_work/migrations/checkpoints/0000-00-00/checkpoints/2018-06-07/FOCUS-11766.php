<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$column_test = Database::$type == 'postgres' ? 'column' : '';

if (!Database::columnExists('gl_pr_termination_codes', 'NO_SHOW')) {
	Database::query('ALTER TABLE gl_pr_termination_codes add ' . $column_test . ' NO_SHOW int');
}

return true;
?>
