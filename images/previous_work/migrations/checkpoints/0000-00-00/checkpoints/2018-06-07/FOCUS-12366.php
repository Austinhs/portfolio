<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::tableExists('gl_pr_school_department_revert')) {
  Database::query(
    "CREATE TABLE gl_pr_school_department_revert (
      ID bigint,
      Staff_id bigint,
      run_id bigint,
      record_ids text,
      type varchar(255)
    )
  ");

  if(Database::$type == 'postgres') {
    Database::createColumn('gl_pr_school_department_revert', 'date_requested', 'timestamp');
  } else {
    Database::createColumn('gl_pr_school_department_revert', 'date_requested', 'datetime');
  }
}
