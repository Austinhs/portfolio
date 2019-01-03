<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Migrations::depend("FOCUS-5470");

Database::begin();

$allow = db_to_char("COALESCE(CAST(u.w4_allowances AS NUMERIC(9,2)), 0.00)", "990.00");
$addn  = db_to_char("COALESCE(CAST(u.w4_additional AS NUMERIC(9,2)), 0.00)", "990.00");
$pre   = Database::$type == "postgres" ? "" : "dbo.";
$sql   = DBEscapeString("
	SELECT    u.staff_id,
	          CONCAT(
	             'Filing ',
	             COALESCE({$pre}FieldOptionLabel(u.w4_status), 'Unknown'),
	             ', ',
	             {$allow},
	             ' Allowances, +',
	             {$addn},
	             CASE WHEN u.w4_exempt IS NULL THEN '' ELSE ', (Exempt)' END
	          ) AS value
	FROM      users u"
);

Database::query("
	UPDATE custom_fields
	SET    computed_query = '{$sql}'
	WHERE  alias = 'es_w4'"
);

Database::commit();

return true;
