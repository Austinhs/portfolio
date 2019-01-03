<?php
$table = 'ps_fa_r2t4';
$output = [];
$columns = [
	['student_id', 'BIGINT'],
	['school_id', 'BIGINT'],
	['syear', 'INT'],
	['calculation_period', 'VARCHAR', 20],
	['determination_at', 'TIMESTAMP'],
	['withdrawal_at', 'TIMESTAMP'],
	['completed_at', 'TIMESTAMP'],
	['created_at', 'TIMESTAMP'],
	['deleted', 'INT'],

	['hours_to_complete', 'NUMERIC', '(10,2)'],
	['hours_in_period', 'INT'],

	['grant_pell_disbursed', 'NUMERIC', '(10,2)'],
	['grant_acg_disbursed', 'NUMERIC', '(10,2)'],
	['grant_smart_disbursed', 'NUMERIC', '(10,2)'],
	['grant_fseog_disbursed', 'NUMERIC', '(10,2)'],
	['grant_teach_disbursed', 'NUMERIC', '(10,2)'],
	['grant_iraq_afghan_service_disbursed', 'NUMERIC', '(10,2)'],
	['grant_pell_possible', 'NUMERIC', '(10,2)'],
	['grant_acg_possible', 'NUMERIC', '(10,2)'],
	['grant_smart_possible', 'NUMERIC', '(10,2)'],
	['grant_fseog_possible', 'NUMERIC', '(10,2)'],
	['grant_teach_possible', 'NUMERIC', '(10,2)'],
	['grant_iraq_afghan_service_possible', 'NUMERIC', '(10,2)'],

	['loan_unsubsidized_disbursed', 'NUMERIC', '(10,2)'],
	['loan_subsidized_disbursed', 'NUMERIC', '(10,2)'],
	['loan_perkins_disbursed', 'NUMERIC', '(10,2)'],
	['loan_student_ffel_disbursed', 'NUMERIC', '(10,2)'],
	['loan_parent_ffel_disbursed', 'NUMERIC', '(10,2)'],
	['loan_unsubsidized_possible', 'NUMERIC', '(10,2)'],
	['loan_subsidized_possible', 'NUMERIC', '(10,2)'],
	['loan_perkins_possible', 'NUMERIC', '(10,2)'],
	['loan_student_ffel_possible', 'NUMERIC', '(10,2)'],
	['loan_parent_ffel_possible', 'NUMERIC', '(10,2)']
];

if (!Database::sequenceExists($table . '_seq')) {
	Database::createSequence($table . '_seq');
	$output[] = "Created sequence for {$table}";
}

if (!Database::tableExists($table)) {
	$create = Database::preprocess("CREATE TABLE {$table} (id INT DEFAULT {{next:{$table}_seq}} PRIMARY KEY)");
	Database::query($create);
	$output[] = "Created table {$table}";
}

foreach ($columns as $column) {
	$columnName   = $column[0];
	$columnType   = $column[1];
	$columnLength = !empty($column[2]) ? $column[2] : null;

	if (!Database::columnExists($table, $columnName)) {
		Database::createColumn($table, $columnName, $columnType, $columnLength);
		$output[] = "Created column {$columnName} on {$table}";
	}
}

if (!empty($output)) {
	echo implode(PHP_EOL, $output);
}
