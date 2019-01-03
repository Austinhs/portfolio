<?php

if(!$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

//mssql version of this migration is FOCUS-9363
if(Database::$type != 'mssql') {
	$queries = [
		'ALTER TABLE ps_1098t_history ALTER COLUMN adjustments TYPE numeric (28,10)',
		'ALTER TABLE ps_1098t_history ALTER COLUMN amount_billed TYPE numeric (28,10)',
		'ALTER TABLE ps_1098t_history ALTER COLUMN payments_received TYPE numeric (28,10)',
		'ALTER TABLE ps_1098t_history ALTER COLUMN scholarship_Adjustments TYPE numeric (28,10)',
		'ALTER TABLE ps_1098t_history ALTER COLUMN scholarships_and_Grants TYPE numeric (28,10)',
	];

	foreach($queries as $query) {
		Database::query($query);
	}
}
?>
