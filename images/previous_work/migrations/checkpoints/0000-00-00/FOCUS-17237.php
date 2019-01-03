<?php

if (Database::tableExists('user_enrollment') && Database:: columnExists('user_enrollment', 'profiles'))
{
	include($GLOBALS['staticpath'] . 'auth/config.php');
	
	$id = PARENT_PROFILE_ID;
	$sql= "
	UPDATE 
		user_enrollment 
	SET 
		profiles = ',{$id},' 
	WHERE 
		profiles = '{$id}'";

	Database::query($sql);
}