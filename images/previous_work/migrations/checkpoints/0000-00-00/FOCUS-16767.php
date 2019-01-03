<?php

// View permission for Create Packages is not necessary.
Database::query("
	DELETE FROM
		permission
	WHERE
		\"key\" = 'Scheduling/CreatePackages.php:can_view'
");
