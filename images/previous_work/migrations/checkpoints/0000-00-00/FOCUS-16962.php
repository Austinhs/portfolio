<?php


if(!Database::columnExists('course_periods', 'disable_auto_template')) {
	Database::createColumn('course_periods', 'disable_auto_template', 'VARCHAR', 1);
}
