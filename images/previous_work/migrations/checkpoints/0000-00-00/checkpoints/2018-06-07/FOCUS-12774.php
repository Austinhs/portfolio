<?php

if (!Database::columnExists("letters", "published_schools")) {
	Database::createColumn("letters", "published_schools", "text");
}

if (!Database::columnExists("letters", "published_profiles")) {
	Database::createColumn("letters", "published_profiles", "text");
}

Database::query("update letters set published_schools = ','".CCAT."cast(school_id as varchar(20))".CCAT."',' where school_id!='0'");

$admin_profiles = Database::get("select id from user_profiles where profile='admin'");

$admin_profile_list = '';
foreach($admin_profiles as $profile)
	$admin_profile_list .= ','.$profile['ID'];
$admin_profile_list .= ',';

Database::query("update letters set published_profiles = '".$admin_profile_list."' where profile='admin'");
