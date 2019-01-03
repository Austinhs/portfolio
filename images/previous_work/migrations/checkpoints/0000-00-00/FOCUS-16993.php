<?php

Database::query("
	UPDATE
		custom_fields
	SET
		default_value = null
	WHERE
		default_value = '[\"\"]'");
