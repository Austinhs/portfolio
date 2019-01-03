<?php

$on_table = Database::$type === 'mssql' ? " ON standards" : '';

if(Database::indexExists('standards','standards_syear_idx'))
	Database::query("drop index standards_syear_idx".$on_table);

if(Database::indexExists('standards','standards_ind5'))
	Database::query("drop index standards_ind5".$on_table);

Database::query("create index standards_ind5 on standards (syear)");

if(!Database::indexExists('standards','standards_ind_rollover_id'))
	Database::query("create index standards_ind_rollover_id on standards (rollover_id)");
