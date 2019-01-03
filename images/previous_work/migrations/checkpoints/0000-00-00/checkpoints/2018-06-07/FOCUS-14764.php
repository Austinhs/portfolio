<?php

Database::begin();

for ($i = 1; $i < 40; $i++) {
	if (Database::columnExists('course_periods', "custom_{$i}")) {
		Database::changeColumnType('course_periods', "custom_{$i}", 'varchar', '255');
	}
}

Database::commit();