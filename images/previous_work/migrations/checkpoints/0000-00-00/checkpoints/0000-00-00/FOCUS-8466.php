<?php 
$table = FAWorksheet::$table;

if (!Database::columnExists($table, 'pell_leu_calculated')) {
	Database::createColumn($table, 'pell_leu_calculated', 'numeric', '', true);
} else {
	Database::changeColumnType($table, 'pell_leu_calculated', 'numeric', '', true);
}

if (!Database::columnExists($table, 'pell_leu')) {
	Database::createColumn($table, 'pell_leu', 'numeric', '', true);
} else {
	Database::changeColumnType($table, 'pell_leu', 'numeric', '', true);
}
