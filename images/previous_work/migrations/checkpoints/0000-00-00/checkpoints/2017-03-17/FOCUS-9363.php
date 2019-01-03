<?php

if(!$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}
if(Database::$type == 'mssql') {
	$queries = [
		'ALTER TABLE ps_1098t_history ALTER COLUMN adjustments numeric (28,10)',
		'ALTER TABLE ps_1098t_history ALTER COLUMN amount_billed numeric (28,10)',
		'ALTER TABLE ps_1098t_history ALTER COLUMN payments_received numeric (28,10)',
		'ALTER TABLE ps_1098t_history ALTER COLUMN scholarship_Adjustments numeric (28,10)',
		'ALTER TABLE ps_1098t_history ALTER COLUMN scholarships_and_Grants numeric (28,10)',
	];

	foreach($queries as $query) {
		Database::query($query);
	}
}
?>
