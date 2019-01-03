<?php

if(!Database::columnExists('course_periods','custom_18'))
	Database::createColumn('course_periods','custom_18','varchar(20)');
