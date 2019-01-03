<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pr_pay_types", "survey_2")) {
	Database::createColumn("gl_pr_pay_types", "survey_2", "int");
}

if (!Database::columnExists("gl_pr_pay_types", "survey_3")) {
	Database::createColumn("gl_pr_pay_types", "survey_3", "int");
}

if (!Database::columnExists("gl_pr_pay_types", "survey_5")) {
	Database::createColumn("gl_pr_pay_types", "survey_5", "int");
}

Database::query("update gl_pr_pay_types set survey_2 = 1, survey_3 = 1, survey_5 = 1
	where pay_type not in ('SI', 'SS', 'V1', 'V2', 'V3', 'V4', 'V5', 'ER', 'XX', 'X8', 'X9')
	");

if (!Database::columnExists("gl_pr_pay_types", "survey_5")) {
	Database::createColumn("gl_pr_pay_types", "survey_5", "int");
}

if (!Database::columnExists("gl_facilities", "doe_code")) {
	Database::createColumn("gl_facilities", "doe_code", "varchar");
}

Database::commit();
return true;
?>
