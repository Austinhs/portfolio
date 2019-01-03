<?php
if(!Database::columnExists('importer_logs', 'validationconversion')) {
	Database::createColumn('importer_logs', 'validationconversion', 'numeric');
}	