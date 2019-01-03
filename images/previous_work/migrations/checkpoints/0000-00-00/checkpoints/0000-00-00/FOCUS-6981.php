<?php
$output = [];
$check = Database::get("SELECT count(id) FROM cron_jobs WHERE title = 'Pay Period 2 Students'");
if (!empty($check)) {
	$sql = "UPDATE cron_jobs SET title = 'Next Unrequested Pay Period Students' WHERE class = 'CTEPayPeriod2CronJob' AND title = 'Pay Period 2 Students'";
	Database::query($sql);
	$output[] = "Renamed 'Pay Period 2 Students' scheduled job to 'Next Unrequested Pay Period Studnets'";
}

$check = Database::get("SELECT count(id) FROM ps_fa_alerts WHERE category = 'pay_period_2'");
if (!empty($check)) {
	$sql = "UPDATE ps_fa_alerts SET category = 'next_pay_period_attended' WHERE category = 'pay_period_2'";
	Database::query($sql);
	$output[] = "Renamed category in ps_fa_alerts from 'pay_period_2' to 'next_pay_period'";
}

if (!empty($output)) {
	echo implode(PHP_EOL, $output);
}
