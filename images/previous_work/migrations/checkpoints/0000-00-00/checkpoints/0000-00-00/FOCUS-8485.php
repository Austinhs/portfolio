<?php
if(!Database::columnExists('integration_export_batches', 'legacy')) {
	Database::createColumn('integration_export_batches', 'legacy', 'int');
}