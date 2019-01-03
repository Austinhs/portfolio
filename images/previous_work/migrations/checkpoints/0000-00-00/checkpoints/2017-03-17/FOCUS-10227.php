<?php

if(CONFIG_DATABASE_TYPE == 'mssql') {
	$RET = Database::get("select d.NAME
		from sys.tables t
		join    sys.default_constraints d
		on d.parent_object_id = t.object_id
		join    sys.columns c
		on c.object_id = t.object_id
		and c.column_id = d.parent_column_id
		where t.name = 'gradebook_assignment_tests'
		and c.name = 'login_pin'");

	foreach($RET as $row)
		Database::query('alter table "gradebook_assignment_tests" drop "'.$row['NAME'].'"');

	$RET = Database::get("select d.name
		from sys.tables t
		join    sys.default_constraints d
		on d.parent_object_id = t.object_id
		join    sys.columns c
		on c.object_id = t.object_id
		and c.column_id = d.parent_column_id
		where t.name = 'fas_student_test_assignment'
		and c.name = 'login_pin'");

	foreach($RET as $row)
		Database::query('alter table "fas_student_test_assignment" drop "'.$row['NAME'].'"');

		$RET = Database::get("select d.name
		from sys.tables t
		join    sys.default_constraints d
		on d.parent_object_id = t.object_id
		join    sys.columns c
		on c.object_id = t.object_id
		and c.column_id = d.parent_column_id
		where t.name = 'fas_test_options'
		and c.name = 'value'");

	foreach($RET as $row)
		Database::query('alter table "fas_test_options" drop "'.$row['NAME'].'"');


}

Database::changeColumnType('gradebook_assignment_tests', 'login_pin', 'TEXT');
Database::changeColumnType('fas_student_test_assignment', 'login_pin', 'TEXT');
Database::changeColumnType('fas_test_options', 'value', 'TEXT');
