<?php

if(!Database::columnExists('master_courses', 'total_credit')) {
	Database::createColumn('master_courses', 'total_credit', 'NUMERIC(2,1)');
}
