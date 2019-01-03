<?php

// Add the displayContinuity preferences if they do not already exist. Set to 0 for Osceola and 1 for all other districts.

global $ClientId;

$sql1 = Database::preprocess("
	INSERT INTO
		school_choice_application_preferences
	SELECT
		{{next:school_choice_application_preferences_seq}}, 'displayContinuity', :display , 'Magnet'
	WHERE NOT EXISTS (
		SELECT 1 FROM
			school_choice_application_preferences
		WHERE
			TITLE = 'displayContinuity'
		AND 
			APP_TYPE = 'Magnet'
	)
");

$sql2 = Database::preprocess("
	INSERT INTO
		school_choice_application_preferences
	SELECT
		{{next:school_choice_application_preferences_seq}}, 'displayContinuity', :display, 'SPA'
	WHERE NOT EXISTS (
		SELECT 1 FROM
			school_choice_application_preferences
		WHERE
			TITLE = 'displayContinuity'
		AND
			APP_TYPE = 'SPA'
	)
");

if ($ClientId === 8473) {
	// Disable the displayContinuity preference for Osceola.
	$params = [
		'display' => 0
	];
}
else {
	$params = [
		'display' => 1
	];
}

Database::query($sql1, $params);
Database::query($sql2, $params);
