<?php

if (Database::tableExists('ps_fee_history') && !Database::columnExists('ps_fee_history','deleted')) {
	Database::query('alter table ps_fee_history add deleted int');
}

if(Database::$type=='postgres') {
	Database::query('alter table master_courses alter column grad_subject_area2 set data type varchar(10)');
	Database::query('alter table master_courses alter column grad_subject_area3 set data type varchar(10)');
}
