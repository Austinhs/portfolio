<?php

if (Database::columnExists('schedule', 'reauthorization_invoice_id')) {
	Database::query("ALTER TABLE schedule DROP COLUMN reauthorization_invoice_id");
}

if (Database::columnExists('schedule', 'reauthorization_schedule_id')) {
	Database::changeColumnType('schedule', 'reauthorization_schedule_id', 'VARCHAR', 255);
}