<?php

$table = 'course_periods';
$column = 'ell_seats';

if(!Database::columnExists($table, $column)) {
	Database::createColumn($table, $column, 'SMALLINT');
}
