<?php

Database::query("
	UPDATE
		program_user_config
	SET
		value = REPLACE(value, 'Assessment/BenchmarkCards.php', 'Manatee/BenchmarkCards.php')
	WHERE
		syear = 2017
		AND value LIKE '%Assessment/BenchmarkCards.php%'
");