<?php
if (Database::tableExists('ps_fa_worksheets') && !Database::columnExists('ps_fa_worksheets', 'isir_id')) {
	Database::createColumn('ps_fa_worksheets', 'isir_id', 'INT');
	Database::changeColumnType('ps_fa_worksheets', 'pell_leu', 'VARCHAR', '10', true);
}

if (Database::tableExists('ps_fa_cod_awards')) {
	if (!Database::columnExists('ps_fa_cod_awards', 'worksheet_id')) {
		Database::createColumn('ps_fa_cod_awards', 'worksheet_id', 'INT');
	}

	if (Database::$type === 'mssql') {
		$type = 'datetime2';
	} else {
		$type = 'timestamp';
	}

	if (!Database::columnExists('ps_fa_cod_awards', 'originated_date')) {
		Database::createColumn('ps_fa_cod_awards', 'originated_date', $type);
	}

	if (!Database::columnExists('ps_fa_cod_disbursements', 'disbursed_date')) {
		Database::createColumn('ps_fa_cod_disbursements', 'disbursed_date', $type);
	}
}

if (Database::tableExists('ps_fa_r2t4')) {
	if (!Database::columnExists('ps_fa_r2t4', 'faworksheet_id')) {
		Database::createColumn('ps_fa_r2t4', 'faworksheet_id', 'INT');
	}

	if (!Database::columnExists('ps_fa_r2t4', 'amount_to_return')) {
		Database::createColumn('ps_fa_r2t4', 'amount_to_return', 'NUMERIC', '(10,2)');
	}

	if (!Database::columnExists('ps_fa_r2t4', 'pay_period_id')) {
		Database::createColumn('ps_fa_r2t4', 'pay_period_id', 'INT');
	}
}
