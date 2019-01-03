<?php

if(Database::tableExists('custom_reports')) {
	if(!Database::columnExists('custom_reports', 'description')) {
		Database::createColumn('custom_reports', 'description', 'text');
	}
}
else {
	return false;
}
