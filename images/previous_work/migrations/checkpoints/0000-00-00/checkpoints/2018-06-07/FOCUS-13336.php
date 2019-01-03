<?php
Database::begin();

//Add columns
if(!Database::columnExists('custom_field_categories', 'sis')) {
	Database::createColumn('custom_field_categories', 'sis', 'smallint');
}
if(!Database::columnExists('custom_field_categories', 'erp')) {
	Database::createColumn('custom_field_categories', 'erp', 'smallint');
}
FocusUser::dropViews();
if(Database::columnExists('users','email')) {
	Database::query('alter table users drop column email');
}
if(!Database::columnExists('users','name_suffix')) {
	Database::createColumn('users','name_suffix','varchar(10)');
}
FocusUser::refreshViews();
//Update new col on existing categories
Database::query("UPDATE custom_field_categories SET SIS = 1, SOURCE_CLASS = 'FocusUser' WHERE SOURCE_CLASS = 'SISUser'");

//Update source class
Database::query("UPDATE CUSTOM_FIELDS SET SOURCE_CLASS = 'FocusUser' WHERE SOURCE_CLASS = 'SISUser'");
Database::query("UPDATE CUSTOM_FIELD_CATEGORIES SET SOURCE_CLASS = 'FocusUser' WHERE SOURCE_CLASS = 'SISUser'");
Database::query("UPDATE CUSTOM_FIELD_LOG_ENTRIES SET SOURCE_CLASS = 'FocusUser' WHERE SOURCE_CLASS = 'SISUser'");

//Update string references to SISUser
Database::query("DELETE FROM PERMISSION WHERE \"key\" LIKE '%FocusUser%'");
Database::query("DELETE FROM USER_PERMISSION WHERE \"key\" LIKE '%FocusUser%'");
Database::query("UPDATE PERMISSION SET \"key\" = REPLACE(\"key\",'SISUser','FocusUser')");
Database::query("UPDATE USER_PERMISSION SET \"key\" = REPLACE(\"key\",'SISUser','FocusUser')");
if (Database::$type == 'mssql') {
	$reports_to_update = Database::get("SELECT ID,QUERY FROM CUSTOM_REPORTS WHERE QUERY LIKE '%SISUser%'");
	foreach ($reports_to_update as $report_to_update) {
		Database::query("UPDATE CUSTOM_REPORTS SET QUERY ='".str_replace('SISUser', 'FocusUser', str_replace("'","''",$report_to_update['QUERY']))."' WHERE ID = {$report_to_update['ID']}");
	}
} else {
	Database::query("UPDATE CUSTOM_REPORTS SET QUERY = REPLACE(QUERY,'SISUser','FocusUser')");
}
Database::query("UPDATE CUSTOM_FIELDS SET OPTION_QUERY = REPLACE(OPTION_QUERY,'SISUser','FocusUser')");
Database::query("UPDATE CUSTOM_FIELD_LOG_COLUMNS SET OPTION_QUERY = REPLACE(OPTION_QUERY,'SISUser','FocusUser')");
Database::query("UPDATE CUSTOM_FIELDS SET COMPUTED_QUERY = REPLACE(COMPUTED_QUERY,'SISUser','FocusUser')");
Database::query("UPDATE CUSTOM_FIELD_LOG_COLUMNS SET COMPUTED_QUERY = REPLACE(COMPUTED_QUERY,'SISUser','FocusUser')");
Database::query("UPDATE CUSTOM_FIELDS SET SUGGESTION_QUERY = REPLACE(SUGGESTION_QUERY,'SISUser','FocusUser')");
Database::query("UPDATE CUSTOM_FIELD_LOG_COLUMNS SET SUGGESTION_QUERY = REPLACE(SUGGESTION_QUERY,'SISUser','FocusUser')");
Database::query("UPDATE EDIT_RULES SET MATCH_SQL = REPLACE(MATCH_SQL,'SISUser','FocusUser')");
Database::query("UPDATE EDIT_RULES SET SQL = REPLACE(SQL,'SISUser','FocusUser')");

Database::commit();