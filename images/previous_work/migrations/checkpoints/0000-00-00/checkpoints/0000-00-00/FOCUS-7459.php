<?php
$table = 'ps_fa_r2t4';
$output = [];

Migrations::depend('FOCUS-7222');
$columns = [
	['period_institutional_charges_tuition', 'NUMERIC', '(10,2)'],
	['period_institutional_charges_room', 'NUMERIC', '(10,2)'],
	['period_institutional_charges_board', 'NUMERIC', '(10,2)'],
	['period_institutional_charges_other1', 'NUMERIC', '(10,2)'],
	['period_institutional_charges_other2', 'NUMERIC', '(10,2)'],
	['period_institutional_charges_other3', 'NUMERIC', '(10,2)'],
	['loan_unsubsidized_school_return', 'NUMERIC', '(10,2)'],
	['loan_subsidized_school_return', 'NUMERIC', '(10,2)'],
	['loan_perkins_school_return', 'NUMERIC', '(10,2)'],
	['loan_student_plus_school_return', 'NUMERIC', '(10,2)'],
	['loan_parent_plus_school_return', 'NUMERIC', '(10,2)'],
	['grant_pell_school_return', 'NUMERIC', '(10,2)'],
	['grant_acg_school_return', 'NUMERIC', '(10,2)'],
	['grant_smart_school_return', 'NUMERIC', '(10,2)'],
	['grant_fseog_school_return', 'NUMERIC', '(10,2)'],
	['grant_teach_school_return', 'NUMERIC', '(10,2)'],
	['grant_iraq_afghan_service_school_return', 'NUMERIC', '(10,2)'],
	['grant_pell_student_return', 'NUMERIC', '(10,2)'],
	['grant_acg_student_return', 'NUMERIC', '(10,2)'],
	['grant_smart_student_return', 'NUMERIC', '(10,2)'],
	['grant_fseog_student_return', 'NUMERIC', '(10,2)'],
	['grant_teach_student_return', 'NUMERIC', '(10,2)'],
	['grant_iraq_afghan_service_student_return', 'NUMERIC', '(10,2)'],
	['allocation_grant_pell_credited', 'NUMERIC', '(10,2)'],
	['allocation_grant_pell_disbursed', 'NUMERIC', '(10,2)'],
	['allocation_grant_acg_credited', 'NUMERIC', '(10,2)'],
	['allocation_grant_acg_disbursed', 'NUMERIC', '(10,2)'],
	['allocation_grant_smart_credited', 'NUMERIC', '(10,2)'],
	['allocation_grant_smart_disbursed', 'NUMERIC', '(10,2)'],
	['allocation_grant_fseog_credited', 'NUMERIC', '(10,2)'],
	['allocation_grant_fseog_disbursed', 'NUMERIC', '(10,2)'],
	['allocation_grant_teach_credited', 'NUMERIC', '(10,2)'],
	['allocation_grant_teach_disbursed', 'NUMERIC', '(10,2)'],
	['allocation_grant_iraq_afghan_service_credited', 'NUMERIC', '(10,2)'],
	['allocation_grant_iraq_afghan_service_disbursed', 'NUMERIC', '(10,2)'],
	['allocation_loan_perkins_seek', 'NUMERIC', '(10,2)'],
	['allocation_loan_perkins_authorized', 'NUMERIC', '(10,2)'],
	['allocation_loan_perkins_credited', 'NUMERIC', '(10,2)'],
	['allocation_loan_perkins_offered', 'NUMERIC', '(10,2)'],
	['allocation_loan_perkins_accepted', 'NUMERIC', '(10,2)'],
	['allocation_loan_perkins_disbursed', 'NUMERIC', '(10,2)'],
	['allocation_loan_sub_seek', 'NUMERIC', '(10,2)'],
	['allocation_loan_sub_authorized', 'NUMERIC', '(10,2)'],
	['allocation_loan_sub_credited', 'NUMERIC', '(10,2)'],
	['allocation_loan_sub_offered', 'NUMERIC', '(10,2)'],
	['allocation_loan_sub_accepted', 'NUMERIC', '(10,2)'],
	['allocation_loan_sub_disbursed', 'NUMERIC', '(10,2)'],
	['allocation_loan_unsub_seek', 'NUMERIC', '(10,2)'],
	['allocation_loan_unsub_authorized', 'NUMERIC', '(10,2)'],
	['allocation_loan_unsub_credited', 'NUMERIC', '(10,2)'],
	['allocation_loan_unsub_offered', 'NUMERIC', '(10,2)'],
	['allocation_loan_unsub_accepted', 'NUMERIC', '(10,2)'],
	['allocation_loan_unsub_disbursed', 'NUMERIC', '(10,2)'],
	['allocation_loan_grad_plus_seek', 'NUMERIC', '(10,2)'],
	['allocation_loan_grad_plus_authorized', 'NUMERIC', '(10,2)'],
	['allocation_loan_grad_plus_credited', 'NUMERIC', '(10,2)'],
	['allocation_loan_grad_plus_offered', 'NUMERIC', '(10,2)'],
	['allocation_loan_grad_plus_accepted', 'NUMERIC', '(10,2)'],
	['allocation_loan_grad_plus_disbursed', 'NUMERIC', '(10,2)'],
	['allocation_loan_parent_plus_seek', 'NUMERIC', '(10,2)'],
	['allocation_loan_parent_plus_authorized', 'NUMERIC', '(10,2)'],
	['allocation_loan_parent_plus_credited', 'NUMERIC', '(10,2)'],
	['allocation_loan_parent_plus_offered', 'NUMERIC', '(10,2)'],
	['allocation_loan_parent_plus_accepted', 'NUMERIC', '(10,2)'],
	['allocation_loan_parent_plus_disbursed', 'NUMERIC', '(10,2)'],
	['notification_sent', 'INT'],
	['response_deadline', 'TIMESTAMP'],
	['response_received', 'INT'],
	['response_received_date', 'TIMESTAMP'],
	['response_not_received', 'INT'],
	['late_response', 'INT'],
	['grant_disbursement_sent_date', 'TIMESTAMP'],
	['loan_disbursement_sent_date', 'TIMESTAMP'],
	['box_1', 'NUMERIC', '(10,2)'],
	['box_2', 'NUMERIC', '(10,2)']
];

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
