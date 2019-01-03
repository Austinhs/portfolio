<?php

if(!Database::columnExists('database_object_log', 'params')) {
	Database::createColumn('database_object_log', 'params', 'text');
}
