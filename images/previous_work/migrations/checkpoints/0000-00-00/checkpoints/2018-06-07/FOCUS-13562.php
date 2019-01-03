<?php
global $ClientId;

Database::query("
	SELECT
		MAX(ID) + 1 as ID into NEWSEQUENCE13562
	FROM
		school_choice_application_preferences;
");

// Check to see if the school_choice_application_preferences table has entries. If the table has no entries, we restart the school_choice_application_preferences_seq sequence using ID 1.
$newSequence = Database::get("
	SELECT
		CASE WHEN ID IS NULL THEN 1 ELSE ID END AS NEWSEQUENCE13562
	FROM NEWSEQUENCE13562;
");

Database::query("DROP TABLE NEWSEQUENCE13562");

$sql = "
	ALTER SEQUENCE
		school_choice_application_preferences_seq
	RESTART WITH {$newSequence[0]['NEWSEQUENCE13562']}
";

// Set the school_choice_application_preferences_seq sequence to the max(ID) + 1 of the school_choice_application_preferences table. If the school_choice_application_preferences table has no max(ID), we restart the sequence using ID 1.
Database::query($sql);

$sql1 = Database::preprocess("
	INSERT INTO
		school_choice_application_preferences
	SELECT
		{{next:school_choice_application_preferences_seq}}, 'displayToursOrAuditions', :display , 'Magnet'
	WHERE NOT EXISTS (
		SELECT 1 FROM
			school_choice_application_preferences
		WHERE
			TITLE = 'displayToursOrAuditions' AND APP_TYPE = 'Magnet'
	)
");

$sql2 = Database::preprocess("
	INSERT INTO
		school_choice_application_preferences
	SELECT
		{{next:school_choice_application_preferences_seq}}, 'displayToursOrAuditions', :display, 'SPA'
	WHERE NOT EXISTS (
		SELECT 1 FROM
			school_choice_application_preferences
		WHERE
			TITLE = 'displayToursOrAuditions' AND APP_TYPE = 'SPA'
	)
");

if ($ClientId === 8473) {
	// Disable the displayToursOrAuditions preferences for Osceola.
	$params = [
		'display' => 0,
	];
} else {
	$params = [
		'display' => 1,
	];
}

// Add the displayToursOrAuditions preferences if they do not already exist. Set to 0 for Osceola and 1 for all other districts.
Database::query($sql1, $params);
Database::query($sql2, $params);