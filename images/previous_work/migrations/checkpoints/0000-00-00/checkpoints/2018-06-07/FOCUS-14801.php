<?php

if(!Database::columnExists('users', 'last_updated_date')) {
	Database::createColumn('users', 'last_updated_date', 'DATE');
}
