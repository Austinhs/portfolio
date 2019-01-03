<?php

Database::query("
	DELETE
	FROM
		program_config
	WHERE
		title = 'COMPLEX_PASSWORDS'
		AND program = 'school_prefs'");
