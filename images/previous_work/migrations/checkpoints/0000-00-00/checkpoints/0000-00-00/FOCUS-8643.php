<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

Database::query("
  UPDATE gl_pr_leave_buckets SET bucket_groups=CONCAT('[\"', bucket_group_id, '\"]')
");
