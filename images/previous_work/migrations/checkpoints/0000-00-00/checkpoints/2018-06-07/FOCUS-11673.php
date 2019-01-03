<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::columnExists('gl_pr_run_control_auto_adjustment', 'percent_change')) {
	Database::createColumn('gl_pr_run_control_auto_adjustment', 'percent_change', 'numeric');
}

if(!Database::columnExists('gl_pr_run_control_auto_adjustment', 'retro_date')) {
	Database::createColumn('gl_pr_run_control_auto_adjustment', 'retro_date', 'date');
}

if(!Database::columnExists('gl_pr_run_control_auto_adjustment', 'overtime')) {
	Database::createColumn('gl_pr_run_control_auto_adjustment', 'overtime', 'numeric');
}

if(!Database::columnExists('gl_pr_run_control_auto_adjustment_overrides', 'overtime')) {
	Database::createColumn('gl_pr_run_control_auto_adjustment_overrides', 'overtime', 'numeric');
}

Database::changeColumnType('gl_pr_why', 'message', 'text');


// if(Database::$type  === 'postgres'){

// 	Database::query("
// 		alter table gl_pr_why alter column message type text
// 	");
// }
// else {
// 	Database::query("
// 		alter table gl_pr_why alter column message text
// 	");
// }

return true;
?>
