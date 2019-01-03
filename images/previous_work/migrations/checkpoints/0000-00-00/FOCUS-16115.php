<?php

Database::query("
	UPDATE
		schedule_requests
	SET
		with_period_id = NULL
	WHERE
		with_period_id = 0;
");

Database::query("
	UPDATE
		schedule_requests
	SET
		not_period_id = NULL
	WHERE
		not_period_id = 0;
");
