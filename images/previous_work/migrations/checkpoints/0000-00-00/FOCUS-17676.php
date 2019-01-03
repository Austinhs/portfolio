<?php
Database::begin();

if (Database::tableExists("gl_requests")) {
	$sql =
		"UPDATE
			gl_requests
		SET
			created_by_class = 'FocusUser'
		WHERE
			created_by_class = 'SISUser'";

	Database::query($sql);
}

Database::commit();
return true;
?>