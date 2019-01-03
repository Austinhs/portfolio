<?php

Migrations::depend('FOCUS-6831');

$output = [];
if (!Database::columnExists('ps_fa_pay_periods', 'first_day_attended')) {
	Database::createColumn('ps_fa_pay_periods', 'first_day_attended', 'TIMESTAMP');
	$output[] = "Add first date attneded column to ps_fa_pay_periods table.";
}

if (!empty($output)) {
	echo implode(PHP_EOL, $output);
}
