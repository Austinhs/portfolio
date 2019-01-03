<?php

if(Database::tableExists('users')) {
	if(!Database::columnExists('users', 'current_selected_profile')) {
		Database::createColumn('users', 'current_selected_profile', 'varchar', 25);
	}
}