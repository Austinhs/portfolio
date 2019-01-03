<?php

Database::query("
	DELETE FROM 
		program_config 
	WHERE 
		syear is NULL              AND 
		school_id is NULL          AND 
		program = 'system'         AND 
		title != 'DEFAULT_S_YEAR'

");