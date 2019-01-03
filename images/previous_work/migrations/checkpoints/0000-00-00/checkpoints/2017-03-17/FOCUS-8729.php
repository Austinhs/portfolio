<?php

if(!Database::columnExists('importer_logs', 'mapping_template')) {
	Database::createColumn('importer_logs', 'mapping_template', 'varchar');
}
