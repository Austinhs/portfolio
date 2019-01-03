<?php

$sql = "
	UPDATE
		students_join_address
	SET
		RESIDENCE = 'Y'
	WHERE
		RESIDENCE = '1'
";

Database::query($sql);

$sql = "
	UPDATE
		students_join_address
	SET
		MAILING = 'Y'
	WHERE
		MAILING = '1'
";
