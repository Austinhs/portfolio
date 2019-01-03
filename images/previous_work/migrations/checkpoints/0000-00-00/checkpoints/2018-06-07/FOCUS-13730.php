<?php
if(!Database::tableExists("workflow_trigger_log")) {
	Database::query("CREATE TABLE workflow_trigger_log (id BIGINT PRIMARY KEY)");
}

if(!Database::tableExists("workflow_trigger_view_log")) {
	Database::query("CREATE TABLE workflow_trigger_view_log (id BIGINT PRIMARY KEY)");
}

if(!Database::sequenceExists('workflow_trigger_log_seq')) {
	Database::createSequence('workflow_trigger_log_seq');
}

if(!Database::sequenceExists('workflow_trigger_view_log_seq')) {
	Database::createSequence('workflow_trigger_view_log_seq');
}

$view_log_columns = [
	'workflow_log_id' => 'BIGINT',
	'staff_id'        => 'BIGINT',
	'viewed_at'       => 'TIMESTAMP',
];

$log_columns = [
	'edit_rule_id'    => 'BIGINT',
	'author_class'    => 'VARCHAR(255)',
	'author_id'       => 'BIGINT',
	'source_class'    => 'VARCHAR(255)',
	'source_id'       => 'BIGINT',
	'school_id'       => 'BIGINT',
	'created_at'      => 'TIMESTAMP',
];

$view_log_indexes = [
	'workflow_log_id' => true,
	'staff_id'        => true,
	'viewed_at'       => true,
];

$log_indexes = [
	'edit_rule_id'    => true,
	'author_class'    => true,
	'author_id'       => true,
	'source_class'    => true,
	'source_id'       => true,
	'school_id'       => true,
	'created_at'      => true,
];

foreach($view_log_columns as $column => $type) {
	if(!Database::columnExists('workflow_trigger_view_log', $column)) {
		Database::createColumn('workflow_trigger_view_log', $column, $type);
	}

	if(!empty($log_indexes[$column]) && !Database::indexExists('workflow_trigger_view_log', "workflow_trigger_view_log_{$column}")) {
		Database::query("CREATE INDEX workflow_trigger_view_log_{$column} ON workflow_trigger_view_log ({$column})");
	}
}

foreach($log_columns as $column => $type) {
	if(!Database::columnExists('workflow_trigger_log', $column)) {
		Database::createColumn("workflow_trigger_log", $column, $type);
	}

	if(!empty($log_indexes[$column]) && !Database::indexExists('workflow_trigger_log', "workflow_trigger_log_{$column}")) {
		Database::query("CREATE INDEX workflow_trigger_log_{$column} ON workflow_trigger_log ({$column})");
	}
}