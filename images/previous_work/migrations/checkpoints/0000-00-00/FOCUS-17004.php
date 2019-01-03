<?php

Migrations::depend('FOCUS-6398');

if(!Database::columnExists('ps_fees', 'one_time_fees')) {
	Database::createColumn('ps_fees', 'one_time_fees', 'bigint');
}
