<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$sql =
	"UPDATE
		gl_dealer
	SET
		old_vendor_number = CONCAT('S', s.custom_53)
	FROM
		students s
	WHERE
		s.student_id = gl_dealer.parent_id AND
		gl_dealer.customer = 1 AND
		gl_dealer.parent_id IS NOT NULL AND
		gl_dealer.parent_class = 'SISStudent'";

Database::query($sql);
Database::commit();
return true;
?>