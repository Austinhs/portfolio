<?php

$username = Authenticate::$internal_focus_user;

$exists = Database::get("
	SELECT
		1
	FROM
		users
	WHERE
		username = '{$username}'
");

if(!empty($exists)) {
	return true;
}

(new FocusUser())
	->setUsername($username)
	->setPassword(sha1(time()))
	->setProfile('admin')
	->persist();