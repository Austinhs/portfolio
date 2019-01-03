<?php

// Remove any ENABLE_INFO_UPDATE_REQUESTS permissions since it's no longer used
Database::query("
	DELETE FROM
		program_config
	WHERE
		title = 'ENABLE_INFO_UPDATE_REQUESTS'
");
