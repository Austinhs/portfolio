<?php
	TableInformation::setTableInfo('gradebook_assignment_tests', 'gat');
	$tableColumns = TableInformation::getColumns('gradebook_assignment_tests');

	if(!in_array('RETAKES', $tableColumns))
		Database::createColumn('gradebook_assignment_tests', 'retakes', 'numeric DEFAULT 0');
	if(!in_array('RETAKES_DELAY', $tableColumns))
		Database::createColumn('gradebook_assignment_tests', 'retakes_delay', 'numeric DEFAULT 0');

?>