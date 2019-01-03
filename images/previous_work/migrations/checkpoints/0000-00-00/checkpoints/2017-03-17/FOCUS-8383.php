<?php
if(Database::$type === 'mssql'){
	if(Database::columnExists('importer_keys', 'post_sql')) {
	    Database::query('Alter table importer_keys drop column post_sql');
	}

	if(Database::columnExists('importer_keys', 'pre_sql')) {
	    Database::query('Alter table importer_keys drop column pre_sql');
	}
}
else{
	if(Database::columnExists('importer_keys', 'post_sql')) {
	    Database::query('Alter table importer_keys drop post_sql');
	}

	if(Database::columnExists('importer_keys', 'pre_sql')) {
	    Database::query('Alter table importer_keys drop pre_sql');
	}
}