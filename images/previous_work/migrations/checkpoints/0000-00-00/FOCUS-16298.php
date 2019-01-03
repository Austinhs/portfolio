<?php
if(!Database::columnExists('application', 'password_token')) {
	Database::createColumn('application', 'password_token', 'varchar', '100');
}
