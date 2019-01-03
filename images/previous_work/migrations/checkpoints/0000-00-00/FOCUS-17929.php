<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$w4_status_col = ERPUser::getFieldByAlias("w4_status");
$w4_allow_col  = ERPUser::getFieldByAlias("w4_allowances");

if (empty($w4_status_col) || empty($w4_allow_col)) {
	return false;
}

$w4_status_col = $w4_status_col["column_name"];
$w4_allow_col  = $w4_allow_col["column_name"];
$w4allow       = db_to_char("COALESCE(CAST(u.{$w4_allow_col} AS NUMERIC(9,2)), 0)", "990");
$schema        = Database::$type == "postgres" ? "" : "dbo.";
$sql           = DBEscapeString("
	SELECT    u.staff_id,
	          RTRIM(
	             CONCAT(
	                'Filing ',
	                COALESCE({$schema}FieldOptionLabel(u.{$w4_status_col}), 'Unknown'),
	                ', ',
	                {$w4allow},
	                ' allowances',
	                CASE WHEN u.w4_exempt IS NULL THEN ' ' ELSE ', (Exempt)' END
	             )
	          ) AS value
	FROM      users u"
);

Database::query("UPDATE custom_fields SET computed_query = '{$sql}' WHERE alias = 'es_w4'");
Database::commit();

return true;
