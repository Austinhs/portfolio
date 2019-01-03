<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}


Database::begin();
for ($i = 1; $i < 13; $i++) {
	$monthValue = $i;
	if($monthValue < 10) {
		$monthValue = '0'.$i;
	}
	
	if(Database::columnExists("gl_pr_leave_schedule", "month_{$monthValue}")) {
		Database::changeColumnType("gl_pr_leave_schedule", "month_{$monthValue}", "numeric", "(28,10)");
	}
}

if(Database::columnExists("gl_pr_leave_schedule", "initial")) {
	Database::changeColumnType("gl_pr_leave_schedule", "initial", "numeric", "(28,10)");
}

Database::commit();
