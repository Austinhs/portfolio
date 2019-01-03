<?php 
// Per Shannon
// Cleanup migration: Removes previously saved setting "Include Inactive" from Assign LCPs scheduled job
// This option is only available on Recalculate LCPs.

Database::begin();

$jobs = ScheduledJob::getAllAndLoad('class = :class', null, ['class' => 'AssignLCP']);
if (!empty($jobs)) {
	foreach ($jobs as &$job) {
		if (!empty($job->getSettings())) {
			$settings = unserialize($job->getSettings());
			if (!empty($settings) && isset($settings['include_inactive'])) {
				unset($settings['include_inactive']);
				try {
					$job
						->setSettings(serialize($settings))
						->persist();
				} catch (Exception $e) {
					return false;
				}
			}
		}
	}
}

Database::commit();
