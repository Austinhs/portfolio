<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_pr_slots", "salary_schedule")) {
	Database::createColumn("gl_pr_slots", "salary_schedule", "char(1)");
}

if (!Database::columnExists("gl_hr_state_reporting", "performance_based_pay")) {
	Database::createColumn("gl_hr_state_reporting", "performance_based_pay", "CHAR(1)");

	Database::query("insert into gl_meta_field (id,title,display_type,meta_table_id,name,type,meta_options_id) values (1,'Performance Pay','select','13711487670765','performance_based_pay','CHAR', 13681219056654)");
	Database::query("insert into gl_meta_field_category (meta_category_id,meta_field_id,id,system) values (14047470976354,1,3,1)");
}


Database::commit();
return true;
?>

