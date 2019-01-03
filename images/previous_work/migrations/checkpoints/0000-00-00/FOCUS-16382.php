<?php

if (Database::tableExists('students_join_people')) {
	Database::query('UPDATE students_join_people SET sort_order = 1 WHERE sort_order = 0');
}