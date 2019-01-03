<?php
if(!Database::tableExists('gl_banks')) {
	return false;
}

if(!Database::columnExists('gl_banks', 'active')) {
	Database::createColumn('gl_banks', 'active', 'integer');

	Database::query("
		UPDATE gl_banks
		SET active = 1
	");
}
