<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$assignee_id_fields = [
	"Permission"     => "profile_id",
	"UserPermission" => "user_id"
];
$permissions        = [
	"ar::post_receipts" => "ar::view_all_receipts"
];

$original_permissions = "'" . implode("', '", array_keys($permissions)) . "'";
$where                = "\"key\" IN ({$original_permissions})";
$profile_permissions  = Permission::getAllAndLoad($where);
$user_permissions     = UserPermission::getAllAndLoad($where);
$all_permissions      = array_merge($profile_permissions, $user_permissions);

foreach ($all_permissions as $permission) {
	$class             = get_class($permission);
	$assignee_id_field = $assignee_id_fields[$class];
	$assignee_id       = $permission->get($assignee_id_field);
	$key               = $permission->getKey();
	$new_permission    = $permissions[$key];

	(new $class)
		->setKey($new_permission)
		->set($assignee_id_field, $assignee_id)
		->persist();
}

Database::commit();
return true;
?>