<?php

Migrations::depend('FOCUS-6359');
SISStudent::dropViews();

foreach(['first_name', 'middle_name', 'last_name'] as $col) {
	Database::changeColumnType('students', $col, 'varchar', '255');
}

SISStudent::refreshViews();
