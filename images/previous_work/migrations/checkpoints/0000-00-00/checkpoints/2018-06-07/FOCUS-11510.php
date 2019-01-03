<?php
if(!Database::columnExists('custom_reports', 'execute_only')) {
	Database::createColumn('custom_reports', 'execute_only', 'integer');
}

