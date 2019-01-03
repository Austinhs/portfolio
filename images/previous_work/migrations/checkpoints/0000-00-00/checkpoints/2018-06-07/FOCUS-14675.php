<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_projects", "migrant_summer")) {
	Database::createColumn("gl_projects", "migrant_summer", "int");
}

if (!Database::columnExists("gl_projects", "migrant_regular_school_year")) {
	Database::createColumn("gl_projects", "migrant_regular_school_year", "int");
}

if (!Database::columnExists("gl_projects", "title_1_school_wide")) {
	Database::createColumn("gl_projects", "title_1_school_wide", "int");
}

if (!Database::columnExists("gl_projects", "title_1_targeted_assistence")) {
	Database::createColumn("gl_projects", "title_1_targeted_assistence", "int");
}

Database::commit();
return true;
?>
