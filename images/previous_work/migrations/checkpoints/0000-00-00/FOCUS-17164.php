<?

Database::query("
	UPDATE
		school_choice_application_status
	SET
		reason = 'M Monitoring'
	WHERE
		reason = 'M Magnet'"
);

Database::query("
	UPDATE
		school_choice_application_status
	SET
		reason = 'S Special Circumstances'
	WHERE
		reason = 'S Catapult'"
);
