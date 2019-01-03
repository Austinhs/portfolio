<?php

if(!Database::columnExists('address', 'mail_plus4')) {
	Database::createColumn('address', 'mail_plus4', 'varchar', '4');
}