<?php
// If old permissions exist, update them to prevent menu permissions needing to be manually updated
$permissions = Permission::getAllAndLoad("\"key\" LIKE 'https://faaaccess.ed.gov/FOTWWebApp/faa/faa.jsp:can_%'");

if (!empty($permissions)) {
	Database::begin();

	foreach ($permissions as $id => $permission) {
		$can    = explode(':can_', $permission->getKey());
		$can    = array_pop($can);
		$newKey = "https://sa.ed.gov/tfa/aimstfa/app/toselfmenu.jsp:can_{$can}";

		$permission
			->setKey($newKey)
			->persist();
	}

	Database::commit();
}
