<?php

Database::query("
	UPDATE
		program_user_config
	SET
		value =
			CASE
				WHEN value = '330099' THEN 'purple'
				WHEN value = 'ED008C' THEN 'pink'
				WHEN value = '3366FF' THEN 'blue'
				WHEN value = '0000FF' THEN 'blue'
				WHEN value = '2E8B57' THEN 'green'
				WHEN value = '003333' THEN 'green'
				WHEN value = 'FFA500' THEN 'yellow'
				WHEN value = 'FF3300' THEN 'orange'
				WHEN value = 'CD0000' THEN 'red'
				WHEN value = '660000' THEN 'red'
				WHEN value = '666666' THEN 'gray'
				WHEN value = '414155' THEN 'gray'
				ELSE value
			END
	WHERE
		program = 'Preferences' AND
		title = 'HIGHLIGHT'
");
