<?php

$sql = "
	UPDATE
		school_choice_application_status
	SET
		status = 0
	WHERE
		status IS NULL
	AND
		CAST(syear AS INT) >= 2018";

Database::query($sql);
