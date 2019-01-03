<?php

Database::query("
	UPDATE custom_fields SET
		system = 1,
		alias='primary_exceptionality'
	WHERE
		column_name = 'custom_890'
");
SISStudent::refreshViews();
