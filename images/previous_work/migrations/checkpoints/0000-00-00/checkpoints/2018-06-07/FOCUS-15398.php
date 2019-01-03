<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::columnExists('gl_pr_leave_buckets', 'hide_zero_balance')) {
	Database::createColumn('gl_pr_leave_buckets', 'hide_zero_balance', 'char', '1');
}
