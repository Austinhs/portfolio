<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::columnExists('gl_pr_leave_bucket_groups', 'change_also')) {
	Database::createColumn('gl_pr_leave_bucket_groups', 'change_also', 'text');
}

if(!Database::columnExists('gl_pr_staff_leave_requests', 'parent_record_id')) {
	Database::createColumn('gl_pr_staff_leave_requests', 'parent_record_id', 'BIGINT');
}

if(!Database::columnExists('gl_pr_staff_leave_requests', 'change_also_starter')) {
	Database::createColumn('gl_pr_staff_leave_requests', 'change_also_starter', 'CHAR', '1');
}

if(!Database::tableExists('db_dump')) {
	Database::query("CREATE TABLE db_dump (id BIGINT PRIMARY KEY NOT NULL, dump TEXT)");
}
