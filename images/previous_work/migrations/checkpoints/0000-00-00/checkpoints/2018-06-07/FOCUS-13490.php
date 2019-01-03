<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$sql =
	"UPDATE
		gl_pos_deferral
	SET
		waived = NULL
	FROM
		gl_pos_payment p
	WHERE
		p.source_id = gl_pos_deferral.id AND
		p.source_class = 'POSDeferral' AND
		COALESCE(gl_pos_deferral.paid, 0) = 0 AND
		p.voided_date IS NOT NULL AND
		COALESCE(gl_pos_deferral.deleted, 0) = 0 AND
		COALESCE(p.deleted, 0) = 0 AND
		gl_pos_deferral.waived = 1";

Database::query($sql);
Database::commit();
return true;
?>