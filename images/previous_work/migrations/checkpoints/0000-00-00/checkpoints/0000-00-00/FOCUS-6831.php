<?php
$output = [];

switch (Database::$type) {
	case 'mssql':
		$dateType  = "DATETIME";
		break;

	default:
		$dateType  = "TIMESTAMP WITHOUT TIME ZONE";
		break;
}

// Begin FAWorksheet Migration
$table_name = 'ps_fa_worksheets';
$sequence_name = 'ps_fa_worksheets_seq';

$renames = [
	'cefc' => 'efc',
	'program_length' => 'program_length_hours',
	'student_transfer_hours' => 'transfer_hours',
	'student_previous_hours' => 'previous_hours',
	'student_previous_paid_hours' => 'previous_paid_hours',
	'student_remaining_hours' => 'remaining_hours',
	'student_remaining_paid_hours' => 'remaining_paid_hours',
	'student_tuition_fees' => 'coa_tuition',
	'student_books_supplies' => 'coa_supplies',
	'student_transportation' => 'coa_transportation',
	'student_misc' => 'coa_misc',
	'student_dependent_care' => 'coa_dependent_care',
	'student_disability_care' => 'coa_disability_care',
	'student_room_board' => 'coa_room_board',
	'student_children_0_6' => 'coa_children0_6',
	'student_children_7_12' => 'coa_children7_12'
];

$creates = [
	'program_length_weeks' => ['INT', '', true],
	'transfer_weeks' => ['INT', '', true],
	'previous_weeks' => ['INT', '', true],
	'previous_paid_weeks' => ['INT', '', true],
	'remaining_weeks' => ['INT', '', true],
	'remaining_paid_weeks' => ['INT', '', true],
	'program_award_year_weeks' => ['INT', '', true],
	'attendance_percent_hours' => ['NUMERIC', '(10,2)', true],
	'attendance_percent_weeks' => ['NUMERIC', '(10,2)', true],
	'amount_used_pell' => ['NUMERIC', '(10,2)', true],
	'auto_zero_flag' => ['VARCHAR', '1', true],
	'notes' => ['TEXT', '', true],
	'overridden_fields' => ['TEXT', '', true]
];

foreach ($renames as $old_column_name => $new_column_name) {
	if (!Database::columnExists($table_name, $new_column_name)) {
		if (Database::columnExists($table_name, $old_column_name)) {
			Database::renameColumn($old_column_name, $new_column_name, $table_name);
		}
	}
}

foreach ($creates as $column_name => $column) {
	if (!Database::columnExists($table_name, $column_name)) {
		Database::createColumn($table_name, $column_name, $column[0], $column[1], $column[2]);
	}
}
// End FAWorksheet Migration


// Pay Period Migration
$table_name    = 'ps_fa_pay_periods';
$sequence_name = 'ps_fa_pay_periods_seq';

switch (Database::$type) {
	case 'mssql':
		$nextValue = "NEXT VALUE FOR datatable_settings_seq";
		break;

	default:
		$nextValue = "NEXTVAL('{$sequence_name}')";
		break;
}

$sql = "
	CREATE TABLE {$table_name} (
		id BIGINT NOT NULL PRIMARY KEY DEFAULT {$nextValue},
		worksheet_id INT NOT NULL,
		period_number INT NOT NULL,
		requested INT NULL,
		hours NUMERIC NULL,
		payment_amount NUMERIC(10,2) NULL,
		expected_pay_date {$dateType} NULL,
		pay_date {$dateType} NULL,
		payment_end_date {$dateType} NULL,
		pell_deferred_amount NUMERIC(10,2) NULL,
		additional_deferred_amount NUMERIC(10,2) NULL,
		net_check NUMERIC(10,2) NULL,
		seog_expected_pay_date {$dateType} NULL,
		seog_expected_pay NUMERIC(10,2) NULL,
		seog_pay_date {$dateType} NULL,
		fsag_ce NUMERIC(10,2) NULL,
		r2t4_date {$dateType} NULL,
		r2t4_amount NUMERIC(10,2) NULL,
		probation INT NULL,
		deleted INT NULL
	)
";

if (!Database::sequenceExists($sequence_name)) {
	Database::createSequence($sequence_name);
	$output[] = "Created Sequence: {$sequence_name}";
}

if (!Database::tableExists($table_name)) {
	Database::query($sql);
	$output[] = "Created Table: {$table_name}";
}
if (Database::tableExists($table_name)) {
	$sql = "
		INSERT INTO ps_fa_pay_periods (
			worksheet_id,
			period_number,
			hours,
			payment_amount,
			expected_pay_date,
			pay_date,
			payment_end_date,
			pell_deferred_amount,
			additional_deferred_amount,
			net_check,
			seog_expected_pay_date,
			seog_expected_pay,
			seog_pay_date,
			fsag_ce,
			probation
		)
		SELECT
		worksheet_id,
		row_number() over(
			PARTITION by worksheet_id
			ORDER BY
				expected_pay_date DESC
		) as period_number,
		payment_period_hours as hours,
		adjusted_gross_pay as payment_amount,
		CAST(expected_pay_date AS {$dateType}),
		CAST(pay_date AS {$dateType}),
		CAST(payment_end_date AS {$dateType}),
		deducted_registration as pell_deferred_amount,
		deducted_bookstore as additional_deferred_amount,
		net_check,
		CAST(seog_expected_pay_date AS {$dateType}),
		seog_expected_pay,
		CAST(seog_pay_date AS {$dateType}),
		fsag_ce,
		(
			CASE probation
			WHEN 'Yes' THEN 1
			WHEN 'Y' THEN 1
			WHEN 'No' THEN 0
			WHEN 'N' THEN 0
			ELSE NULL
			END
		) as probation
		FROM ps_fa_worksheet_table_data
	";
	Database::query($sql);
	$output[] = "Migrated payment period data";
}
// End Pay Period Migration

$dropConstrains = [
	'ps_fa_worksheet_fields_fkey1' => 'ps_fa_worksheet_fields',
	'ps_fa_worksheet_table_data_adjustments_fkey1' => 'ps_fa_worksheet_table_data_adjustments',
	'ps_fa_worksheet_table_data_fkey1' => 'ps_fa_worksheet_table_data'
];
foreach ($dropConstrains as $key => $table) {
	$fKeys = Database::getForeignKeys($table);
	if (!empty($fKeys)) {
		Database::query("ALTER TABLE {$table} DROP CONSTRAINT {$key}");
	}
}

$permissions = [
	'fafsa/FAWorksheet.php:can_view' => 'fafsa/FAWorksheet/View.php:can_view',
	'fafsa/FAWorksheet.php:can_edit' => 'fafsa/FAWorksheet/View.php:can_edit'
];

foreach ($permissions as $oldKey => $newKey) {
	$checkSql = Database::get("SELECT 1 FROM user_permission WHERE \"key\" = '{$newKey}'");
	if (empty($checkSql)) {
		$output[] = "FAWorksheet Permissions migrated in table user_permission";
		Database::query("UPDATE user_permission SET \"key\" = '{$newKey}' WHERE \"key\" = '{$oldKey}'");
	}

	$checkSql = Database::get("SELECT 1 FROM permission WHERE \"key\" = '{$newKey}'");
	if (empty($checkSql)) {
		$output[] = "FAWorksheet Permissions migrated in table permission";
		Database::query("UPDATE permission SET \"key\" = '{$newKey}' WHERE \"key\" = '{$oldKey}'");
	}
}

echo implode("\n", $output);
