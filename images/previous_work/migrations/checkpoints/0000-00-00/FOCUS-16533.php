<?php

for ($i = 1; $i <= 80; $i++) {
	$column = "custom_{$i}";

	if(!Database::columnExists('schedule', $column)) {
		Database::createColumn('schedule', $column, 'varchar', '255');
	}
}
