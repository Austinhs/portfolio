<?php
// Tags: SSS

if (!Database::tableExists('sss_accommodations_other')) {
	return false;
}

if (!Database::columnExists('sss_accommodations_other', 'schedule')) {
	Database::createColumn('sss_accommodations_other', 'schedule', 'varchar', 255);
}
