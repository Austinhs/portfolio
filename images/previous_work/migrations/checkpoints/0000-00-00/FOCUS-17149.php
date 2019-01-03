<?php
	if(!Database::columnExists('student_groups', 'schools')) {
		Database::createColumn('student_groups', 'schools', 'VARCHAR(4095)');
	}