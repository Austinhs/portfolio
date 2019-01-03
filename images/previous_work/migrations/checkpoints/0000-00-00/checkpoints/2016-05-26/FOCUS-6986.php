<?php

$sql = "
	UPDATE
		edit_rules
	SET
		type = 'validation'
	WHERE
		type IS NULL
";

Database::query($sql);
