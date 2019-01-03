<?php

$sql = "
	UPDATE
		header_templates
	SET
		available_for = NULL
	WHERE
		available_for = '||||'
";

Database::query($sql);