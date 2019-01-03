<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::columnExists("gl_ba_check_totals", "insurance_qual")) {
	Database::createColumn("gl_ba_check_totals", "insurance_qual", "NUMERIC");
}

if (!Database::columnExists("gl_ba_check_totals", "medicare_qual")) {
	Database::createColumn("gl_ba_check_totals", "medicare_qual", "NUMERIC");
}

if (!Database::columnExists("gl_ba_check_totals", "retirement_qual")) {
	Database::createColumn("gl_ba_check_totals", "retirement_qual", "NUMERIC");
}

if (!Database::columnExists("gl_ba_check_totals", "social_security_qual")) {
	Database::createColumn("gl_ba_check_totals", "social_security_qual", "NUMERIC");
}

return true;
