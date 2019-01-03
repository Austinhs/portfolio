<?php
Database::begin();

if (!Database::columnExists('ps_fa_saig_files', 'imported_at')) {
	if (Database::$type === 'mssql') {
		Database::query('ALTER TABLE ps_fa_saig_files DROP COLUMN transfer_at');
		Database::createColumn('ps_fa_saig_files', 'transfer_at', 'TIMESTAMP', null, true);
	}
	Database::createColumn('ps_fa_saig_files', 'imported_at', 'TIMESTAMP', null, true);
	Database::query("UPDATE ps_fa_saig_files SET imported_at = transfer_at WHERE transfer_type = 'R' AND imported = 1 AND transfer_at IS NOT NULL");
}

Database::commit();
