<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$setting_map = [
	0 => "out_of_pocket",
	1 => "deferrals"
];
$current     = (int) Settings::get("payments_received");
$client_id   = (int) $GLOBALS["ClientId"];
$new_setting = ($client_id === 6140005774) ? "all_payments" : $setting_map[$current];

Settings::set("payments_received", $new_setting);
Database::commit();
return true;
?>