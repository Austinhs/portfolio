<?php
// Tags: SSS

if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
	return false;
}

Database::begin();

if(!Database::columnExists('sss_services', 'how')) {
	Database::createColumn('sss_services', 'how', 'varchar', 255);
}

// RISD stored this when they had SSS, maybe they want to keep the data
if ($ClientId !== 20862 && Database::columnExists('sss_services', 'modified_instruction')) {
	Database::dropColumn('sss_services', 'modified_instruction');
}

Database::commit();
