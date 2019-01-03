<?php

if(!Database::columnExists('discipline_referrals', 'submission_date')) {
	if(Database::$type === 'postgres') {
		Database::createColumn('discipline_referrals', 'submission_date', 'TIMESTAMP');
	}

	else {
		Database::createColumn('discipline_referrals', 'submission_date', 'DATETIME');
	}

	if(Database::$type === 'postgres') {
		$query = '
			ALTER TABLE
				discipline_referrals
			ALTER COLUMN
				submission_date
			SET DEFAULT
				CURRENT_TIMESTAMP
		';
	}

	else {
		$query = '
			ALTER TABLE
				discipline_referrals
			ADD DEFAULT
				GETDATE()
			FOR
				submission_date
		';
	}

	$sql = "
		UPDATE
			discipline_referrals
		SET
			SUBMISSION_DATE = ENTRY_DATE
		WHERE
			SUBMISSION_DATE IS NULL
	";

	Database::query($sql);
	Database::query($query);
}