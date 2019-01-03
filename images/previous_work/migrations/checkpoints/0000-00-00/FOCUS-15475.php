<?php

if(!Database::columnExists('schedule_requests', 'schedule_first')) {
	Database::createColumn('schedule_requests', 'schedule_first', 'BIGINT');
}
