<?php 

if (Database::tableExists('ps_fa_alerts')){
	if (!Database::columnExists('ps_fa_alerts', 'profiles')) {
		Database::createColumn('ps_fa_alerts', 'profiles', 'TEXT');
	}
} else {
	throw new Exception("Missing ps_fa_alerts table, 7.3.2.sql needs to be run.");
}
