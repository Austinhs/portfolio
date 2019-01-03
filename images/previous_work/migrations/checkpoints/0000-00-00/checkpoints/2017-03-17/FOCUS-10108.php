<?php
Migrations::depend('FOCUS-8980');
Database::begin();
$sql = "
	update 
		importer_templates 
	set 
		name = 'Attendance Calendar' 
	where 
		name = 'Attendance Calander';
";

if (Database::tableExists('importer_templates')){
	Database::query($sql);
}

if (Database::tableExists('import_matched_students_file_info')) {
	if (!Database::columnExists('import_matched_students_file_info', 'file_name')) {
		Database::createColumn('import_matched_students_file_info', 'file_name', 'text');
	} else {
		Database::changeColumnType('import_matched_students_file_info', 'file_name', 'text', '', true);
	}

	if (!Database::columnExists('import_matched_students_file_info', 'original_file_name')) {
		Database::createColumn('import_matched_students_file_info', 'original_file_name', 'text');
	} else {
		Database::changeColumnType('import_matched_students_file_info', 'original_file_name', 'text', '', true);
	}
}

if (Database::tableExists('match_students_importer_table')) {
	if (!Database::columnExists('match_students_importer_table', 'file_name')) {
		Database::createColumn('match_students_importer_table', 'file_name', 'text');
	} else {
		Database::changeColumnType('match_students_importer_table', 'file_name', 'text', '', true);
	}

	if (!Database::columnExists('match_students_importer_table', 'original_file_name')) {
		Database::createColumn('match_students_importer_table', 'original_file_name', 'text');
	} else {
		Database::changeColumnType('match_students_importer_table', 'original_file_name', 'text', '', true);
	}
}

$value_exist_sql="SELECT 
						NULL 
					FROM 
						importer_keys 
					WHERE 
						table_name = 'address_to_district'";

$value_exist=Database::get($value_exist_sql);

if (count($value_exist)==0) {
	$sql = "INSERT INTO importer_keys (TABLE_NAME, IDENTITY_COLUMN, PRIMARY_KEYS, REQUIRED_FIELDS) 
				VALUES 
					(
						'address_to_district', 'id', '[]','[\"syear\"]'
					)";
	Database::query($sql);
}


$value_exist_sql="SELECT 
					NULL 
				FROM 
					importer_templates 
				WHERE 
					name = 'Address Catalog' 
					AND TYPE = 'main'";

$value_exist=Database::get($value_exist_sql);

if (count($value_exist)==0) {
	$sql = "INSERT INTO importer_templates (NAME, TYPE, SETTINGS) 
				VALUES 
					(
						'Address Catalog', 'main','{\"destinationTable\":\"address_to_district\",\"temporaryTable\":\"addressToDistrictTempImporter\",\"errorTable\":\"addressToDistrictTempImporter_error\",\"primaryKeys\":[],\"identityColumn\":\"none\"}'
					)";
	Database::query($sql);
}

if(!Database::columnExists('match_students_importer_table','imported')){
	Database::createColumn('match_students_importer_table', 'imported', 'varchar',1);
}

Database::commit();
