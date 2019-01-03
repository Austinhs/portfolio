<?php
if(!Database::columnExists('school_choice_application_fields', 'sort_order')) {
	Database::createColumn('school_choice_application_fields', 'sort_order', 'integer');
}