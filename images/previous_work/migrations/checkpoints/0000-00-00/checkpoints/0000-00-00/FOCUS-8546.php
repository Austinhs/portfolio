<?php

if(Database::$type == 'mssql' && Database::tableExists('ps_fee_history')) {
	$queries = [
		"ALTER TABLE ps_fee_history ALTER COLUMN program_number varchar(255)",
		"ALTER TABLE ps_fee_history ALTER COLUMN period_id varchar(255)"
	];

	foreach($queries as $query) {
		Database::query($query);
	}
}
else {
	return false;
}
 ?>
