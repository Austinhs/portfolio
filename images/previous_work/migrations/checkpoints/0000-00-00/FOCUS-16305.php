<?php

if (!Database::columnExists('schedule', 'effective_init_func_level')) {
	Database::createColumn('schedule', 'effective_init_func_level', 'varchar', '255');
}
