<?php

Migrations::depend('FOCUS-7905');

if(!Database::columnExists('custom_reports', 'package')){
	Database::createColumn('custom_reports', 'package', 'varchar');
	Database::query("UPDATE custom_reports set package = 'SIS'");
}
if(!Database::columnExists('custom_reports_folders', 'package')){
	Database::createColumn('custom_reports_folders', 'package', 'varchar');
	Database::query("UPDATE custom_reports_folders set package = 'SIS'");
}
if(!Database::columnExists('custom_reports_variables', 'package')){
	Database::createColumn('custom_reports_variables', 'package', 'varchar');
	Database::query("UPDATE custom_reports_variables set package = 'SIS'");
}