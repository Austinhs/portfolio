<?php
//Jira 17045

if (empty($GLOBALS['FocusFinanceConfig']['enabled'])) {
	return false;
}


Database::begin();

if (!Database::columnExists('gl_pr_florida_salary_adjustments', 'source_process')) {
	Database::createColumn('gl_pr_florida_salary_adjustments', 'source_process', 'varchar(50)');
}

Database::commit();
return true;
