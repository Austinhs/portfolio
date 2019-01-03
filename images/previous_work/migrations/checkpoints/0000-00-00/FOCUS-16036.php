<?php

if(!Database::columnExists('teacher_comments', 'multiple_comments')) {
	Database::createColumn('teacher_comments', 'multiple_comments', 'varchar', '255');
}
