<?php
// Get recent default syear
$sql = "SELECT
			VALUE AS SYEAR
		FROM
			PROGRAM_CONFIG
		WHERE
			TITLE = 'DEFAULT_S_YEAR'
		ORDER BY 
			TITLE DESC";

$result = Database::get($sql);
$syear = !empty($result) ? $result[0]['SYEAR'] : '';

if (Database::columnExists("ps_fa_alerts", "syear") && !empty($syear)) {

	// Clean up previous school years to get proper results in cron job
	$delete_sql = "DELETE FROM ps_fa_alerts WHERE SYEAR NOT IN ({$syear})";

	Database::query($delete_sql);

	$drop_col_sql = "ALTER TABLE ps_fa_alerts DROP COLUMN syear";

	Database::query($drop_col_sql);
}