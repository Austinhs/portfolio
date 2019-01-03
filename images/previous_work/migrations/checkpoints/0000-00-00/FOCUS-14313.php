<?php
// Tags: Formbuilder
Database::begin();

if(!Database::tableExists('formbuilder_actions')) {
	Database::createSequence('formbuilder_actions_id_seq');
	Database::query(Database::preprocess("
		CREATE TABLE formbuilder_actions (
			id BIGINT PRIMARY KEY DEFAULT {{next:formbuilder_actions_id_seq}} NOT NULL
		)
	"));
}

if (!Database::columnExists('formbuilder_actions', 'created_revision')) {
	Database::createColumn('formbuilder_actions', 'created_revision', 'bigint');
}

if (!Database::columnExists('formbuilder_actions', 'removed_revision')) {
	Database::createColumn('formbuilder_actions', 'removed_revision', 'bigint');
}

if (!Database::columnExists('formbuilder_actions', 'form_id')) {
	Database::createColumn('formbuilder_actions', 'form_id', 'bigint');
}

if (!Database::columnExists('formbuilder_actions', 'name')) {
	Database::createColumn('formbuilder_actions', 'name', 'varchar', '255');
}

if (!Database::columnExists('formbuilder_actions', 'query')) {
	Database::createColumn('formbuilder_actions', 'query', 'text');
}

if (!Database::columnExists('formbuilder_actions', 'run_on')) {
	Database::createColumn('formbuilder_actions', 'run_on', 'varchar', '255');
}

Database::commit();