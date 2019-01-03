<?php

Database::get("
	UPDATE
		cron_jobs
	SET
		title = 'Portal Payments Automated Cashout'
	WHERE
		class = 'StoreAutoCashoutCronJob'
");