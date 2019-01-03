<?php

// Tags: SSS

if (!Database::tableExists('sss_goals')) {
	return false;
}

if (!Database::columnExists('sss_goals', 'progress_frequency')) {
	Database::createColumn('sss_goals', 'progress_frequency', 'varchar', 255);
}
