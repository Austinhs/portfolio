<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pr_slots", "additional_comp")) {
	Database::createColumn("gl_pr_slots", "additional_comp", "CHAR(1)");
}

if (!Database::columnExists("gl_pr_positions", "fl_fund_source_1")) {
	Database::createColumn("gl_pr_positions", "fl_fund_source_1", 'char', 1);
}

if (!Database::columnExists("gl_pr_positions", "fl_fund_source_2")) {
	Database::createColumn("gl_pr_positions", "fl_fund_source_2", 'char', 1);
}

if (!Database::columnExists("gl_pr_positions", "fl_fund_source_3")) {
	Database::createColumn("gl_pr_positions", "fl_fund_source_3", 'char', 1);
}

if (!Database::columnExists("gl_pr_positions", "fl_source_perc_1")) {
	Database::createColumn("gl_pr_positions", "fl_source_perc_1", 'numeric(28,10)');
}

if (!Database::columnExists("gl_pr_positions", "fl_source_perc_2")) {
	Database::createColumn("gl_pr_positions", "fl_source_perc_2", 'numeric(28,10)');
}

if (!Database::columnExists("gl_pr_positions", "fl_source_perc_3")) {
	Database::createColumn("gl_pr_positions", "fl_source_perc_3", 'numeric(28,10)');
}

if (!Database::columnExists("gl_pr_slots", "advanced_degree")) {
	Database::createColumn("gl_pr_slots", "advanced_degree", "char(1)");
}


if (!Database::columnExists("gl_projects", "fl_fund_source_1")) {
	Database::createColumn("gl_projects", "fl_fund_source_1", 'char', 1);
}


if (!Database::columnExists("gl_projects", "fl_source_perc_1")) {
	Database::createColumn("gl_projects", "fl_source_perc_1", 'numeric(28,10)');
}







Database::commit();


return true;
?>
