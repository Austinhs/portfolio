<?php

if(!Database::columnExists('custom_field_log_entries', 'syear')) {
	Database::createColumn('custom_field_log_entries', 'syear', 'numeric');
}
