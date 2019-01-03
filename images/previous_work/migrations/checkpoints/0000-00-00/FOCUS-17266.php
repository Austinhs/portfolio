<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

Database::query("
	UPDATE custom_fields
	SET    computed_query = 'SELECT u.staff_id, CONCAT(''Filing '', COALESCE(cfso.label, ''Unknown''), '', '', TO_CHAR(COALESCE(CAST(u.w4_allowances AS NUMERIC(9,2)), 0.00), ''990.00''), '' Allowances, +'', TO_CHAR(COALESCE(CAST(u.w4_additional AS NUMERIC(9,2)), 0.00), ''990.00''), CASE WHEN u.w4_exempt IS NULL THEN '''' ELSE '', (Exempt)'' END ) AS value FROM users u LEFT JOIN custom_fields cf ON cf.alias = ''w4_status'' LEFT JOIN custom_field_select_options cfso ON cfso.source_id = cf.id AND cfso.source_class = ''CustomField'' AND cfso.id = u.w4_status'
	WHERE  alias = 'es_w4'"
);

Database::commit();

return true;
