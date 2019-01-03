<?php

$sql = "
	SELECT
		MAX(id) AS ID
	FROM
		permission;";

$new_id = Database::get($sql);
$new_id = $new_id[0]['ID'] + 1;

$sql = "ALTER SEQUENCE permission_seq RESTART WITH {$new_id}";

Database::query($sql);

$sql = Database::preprocess("
	UPDATE
		permission
	SET
		id = {{next:permission_seq}}
	WHERE
		id IN (
			SELECT
				id
			FROM
				permission
			GROUP BY
				id
			HAVING
				COUNT(1) > 1
		)
");

Database::query($sql);

if(Database::getPrimaryKey('permission') !== 'id') {
	$sql = 'ALTER TABLE permission
	ADD PRIMARY KEY (id)';

	Database::query($sql);
}