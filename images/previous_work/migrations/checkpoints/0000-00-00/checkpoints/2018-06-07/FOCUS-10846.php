<?php

Database::query("
	DELETE
		FROM
			CRON_JOBS
		WHERE
			CLASS = 'InvoiceReleaseFlowCronJob'
");

?>
