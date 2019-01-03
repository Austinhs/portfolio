<?php
if(!Database::columnExists('school_choice_application_fields', 'app_type')){
	Database::createColumn('school_choice_application_fields', 'app_type', 'varchar', '50');
	Database::query("update school_choice_application_fields set app_type='Magnet' ");
}
if(!Database::columnExists('school_choice_application_notes', 'app_type')){
	Database::createColumn('school_choice_application_notes', 'app_type', 'varchar', '50');
	Database::query("update school_choice_application_notes set app_type='Magnet' ");
}
if(!Database::columnExists('school_choice_applications', 'app_type')){
	Database::createColumn('school_choice_applications', 'app_type', 'varchar', '50');
}
if(!Database::columnExists('school_choice_application_status', 'app_type')){
	Database::createColumn('school_choice_application_status', 'app_type', 'varchar', '50');
}

if(!Database::tableExists('school_choice_application_preferences')){
	Database::query('
		CREATE TABLE school_choice_application_preferences (
			id BIGINT NOT NULL PRIMARY KEY,
			title VARCHAR,
			value VARCHAR,
			app_type VARCHAR(50)
		)
	');
	Database::createSequence('school_choice_application_preferences_seq');
}

if(empty(Database::get("select * from school_choice_priorities where abbr = 'DE'"))){
	Database::query("insert into school_choice_priorities (abbr, title) values ('DE', 'District Employee')");
}
if(empty(Database::get("select * from school_choice_priorities where abbr = 'SC'"))){
	Database::query("insert into school_choice_priorities (abbr, title) values ('SC', 'Special Circumstances')");
}

Database::changeColumnType('school_choice_programs', 'additional_requirements', 'VARCHAR', '1750');
