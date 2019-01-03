<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pr_pay_types", "survey_8")) {
	Database::createColumn("gl_pr_pay_types", "survey_8", "int");
}

Database::query("update gl_pr_pay_types set survey_8 = 1
	where pay_type not in ('SI', 'SS', 'V1', 'V2', 'V3', 'V4', 'V5', 'ER', 'XX', 'X8', 'X9')
	");

Database::commit();
return true;
?>
