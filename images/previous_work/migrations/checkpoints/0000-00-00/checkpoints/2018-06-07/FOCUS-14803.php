<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if(!Database::columnExists('gl_pr_staff_job_allocations', 'contract_year')) {
		Database::createColumn('gl_pr_staff_job_allocations', 'contract_year', 'bigint');
}

if(Database::columnExists('gl_pr_staff_job_allocations', 'SRC_STAGE') && Database::columnExists('gl_pr_staff_job_allocations', 'SRC_ROW_ID')) {
	$src_staging = true;
} else {
	$src_staging = false;
}

$currentAllocations = Database::get("SELECT * FROM gl_pr_staff_job_allocations");

$insertHistory = [];
foreach($currentAllocations as $record) {
	$contract_year = PRGeneral::getContractYear($record['DATE_EFFECTIVE']);

	if(!$record['DATE_STOP']) {
		$current_contract_year = PRGeneral::getContractYear();

		if($current_contract_year > $contract_year) {
			$insert = $record;
			unset($insert['ID']);
			unset($insert['IMPORT_LINE']);

			if($src_staging) {
				unset($insert['SRC_ROW_ID']);
				$insert['SRC_STAGE']     = 'FOCUS-14803';
			}
			
			$insert['CONTRACT_YEAR'] = $contract_year;
			$insert['DATE_STOP']     = PRGeneral::getContractEndingDate($contract_year);

			$insertHistory[] = $insert;
		}

		$current_year_start_date = PRGeneral::getContractStartingDate($current_contract_year);
		Database::query(
			"UPDATE gl_pr_staff_job_allocations
				SET
					date_effective = '{$current_year_start_date}',
					contract_year = {$current_contract_year}
			WHERE id = {$record['ID']}
		");
	} else {
		Database::query(
			"UPDATE gl_pr_staff_job_allocations
			SET contract_year = {$contract_year}
			WHERE id = {$record['ID']}
		");
	}
}

if($insertHistory) {
	$insert_retval = Database::insert(
		StaffJobAllocation::$table,
		StaffJobAllocation::$sequence,
		array_keys($insertHistory[0]),
		$insertHistory
	);
}

Database::commit();
