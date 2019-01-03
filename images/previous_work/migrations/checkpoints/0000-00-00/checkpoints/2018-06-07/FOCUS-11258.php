<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if (!Database::tableExists("gl_pos_refund")) {
	return false;
}

if (file_exists(__DIR__ . "/FOCUS-12493.php")) {
	Migrations::depend("FOCUS-12493");
}

Database::begin();
Database::createColumn("gl_pos_refund", "subtotal", "NUMERIC", "(28,10)");
Database::createColumn("gl_pos_refund", "state_tax", "NUMERIC", "(28,10)");
Database::createColumn("gl_pos_refund", "local_tax", "NUMERIC", "(28,10)");

$refunds = POSRefund::getAllAndLoad([
	"COALESCE(deleted, 0) = 0"
]);

foreach ($refunds as $refund) {
	$refund->persist();
}

Database::commit();

return true;
?>