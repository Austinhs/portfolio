<?php


if(Database::$type != 'mssql' && Database::tableExists('ps_fee_history')) {
	$queries = [
		"ALTER TABLE ps_fee_history ALTER COLUMN program_number type varchar(255)",
		"ALTER TABLE ps_fee_history ALTER COLUMN period_id type varchar(255)"
	];

	foreach($queries as $query) {
		Database::query($query);
	}
}
else {
	return false;
}
