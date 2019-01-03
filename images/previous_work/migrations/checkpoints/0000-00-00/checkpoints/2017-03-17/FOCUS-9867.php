<?php
// People Skype me all day when migrations fail on non-ERP sites
if (!$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return;
}

Database::begin();

if (!Database::columnExists("gl_ap_vendor_payment", "form_box_1099")) {
	Database::createColumn("gl_ap_vendor_payment", "form_box_1099", "CHAR");
}

$sql = 
	"UPDATE
		gl_ap_vendor_payment
	SET 
		form_box_1099 = '7'
	WHERE 
		COALESCE(form_box_1099, '0') = '0'";

Database::query($sql);
Database::commit();
?>