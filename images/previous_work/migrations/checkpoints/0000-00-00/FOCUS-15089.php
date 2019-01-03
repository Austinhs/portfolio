<?php

Database::changeColumnType('school_choice_application_preferences', 'title', 'VARCHAR', 255);

if (!Database::indexExists('school_choice_application_preferences', 'school_choice_application_preferences_ind1')) {
	$sql = '
		CREATE INDEX
			school_choice_application_preferences_ind1
		ON
			school_choice_application_preferences (app_type, title)';

	Database::query($sql);
}
