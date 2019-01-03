<?php

//Step 1, create column, add if exist

if(!Database::columnExists('integration_export_batches', 'legacy')) {
	Database::query('alter table integration_export_batches add legacy int null;');
}

//Step 2, populate column if legacy

Database::query('UPDATE integration_export_batches SET legacy = 1 WHERE lower(data) like lower(\'%/Focus_Libraries/Integration/IntegrationAPI/batches/%\');');


$legacy_batches = Database::get("select id from integration_export_batches where legacy is not null");
$legacy_ids     = [];

foreach ($legacy_batches as $batch) {
        $legacy_ids[$batch['ID']] = true;
}

$cron_jobs = Database::get("select * from cron_jobs");

foreach ($cron_jobs as $job) {
        $cron_settings = unserialize($job['SETTINGS']);
        $batch_id      = isset($cron_settings['batchID']) ? $cron_settings['batchID'] : '';

        if (!empty($legacy_ids[$batch_id])) {
                echo ("DELETE FROM cron_jobs WHERE id={$job['ID']}");
        }
}
