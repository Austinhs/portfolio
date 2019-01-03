<?php
if(!Database::columnExists('integration_export_batches', 'package')) {
	Database::createColumn('integration_export_batches', 'package', 'varchar');
}
Database::Query("Update integration_export_batches set package = 'SIS' where package is null ");

// this comes from 6874, but I'm putting the column in v7 to avoid conflicts
if(!Database::columnExists('integration_export_batches', 'legacy')) {
	Database::query('alter table integration_export_batches add legacy int null;');
}
