<?php

if (!Database::indexExists('schedule', 'schedule_inclusion_ind')) {
	Database::query("create index schedule_inclusion_ind on schedule (inclusion) where inclusion is not null");
}
