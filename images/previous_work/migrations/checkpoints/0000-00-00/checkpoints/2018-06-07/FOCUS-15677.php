<?php

// This is Loren's migration from FOCUS-13341

// found that some databases already have SISStudent:students entries
$sql1 = "update permission set \"key\" = REPLACE(permission.\"key\",':users|', ':students|') where \"key\" like 'SISStudent:users|%' and not exists (select 1 from permission p where p.\"key\" = REPLACE(permission.\"key\",':users|', ':students|') and p.profile_id = permission.profile_id)";

$sql2 = "update database_object_log set params = REPLACE(params,':users|', ':students|') where params like '%SISStudent:users|force_password_change%'";
$sql3 = "update database_object_log set after  = REPLACE(after,':users|',  ':students|') where after  like '%SISStudent:users|force_password_change%'";

// remove the SISStudent:users entries
$sql4 = "delete from permission where \"key\" like 'SISStudent:users|%'";


Database::query($sql1);
Database::query($sql2);
Database::query($sql3);
Database::query($sql4);
