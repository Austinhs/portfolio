<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"])
	return false;

Database::begin();

if (Database::tableExists("florida_survey_dates"))
{
	$res = Database::get("SELECT DISTINCT survey FROM florida_survey_dates");

	foreach ($res as $r)
		if (!Database::columnExists("gl_pr_pay_types", "survey_{$r["SURVEY"]}"))
			Database::createColumn("gl_pr_pay_types", "survey_{$r["SURVEY"]}", "BIGINT");
}

Database::commit();

return true;
