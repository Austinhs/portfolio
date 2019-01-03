<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_budget_scenario", "salary_amount")) {
	Database::createColumn("gl_budget_scenario", "salary_amount", "NUMERIC", "(28,10)");
}

if (!Database::columnExists("gl_budget_scenario", "deduction_amount")) {
	Database::createColumn("gl_budget_scenario", "deduction_amount", "NUMERIC", "(28,10)");
}

if (!Database::columnExists("gl_budget_scenario_budget", "payroll")) {
	Database::createColumn("gl_budget_scenario_budget", "payroll", "SMALLINT");
}

if (Database::$type === "mssql") {
	$sql = "IF OBJECT_ID('LEAST', 'FN') IS NOT NULL DROP FUNCTION dbo.LEAST";

	Database::query($sql);

	$sql =
		"CREATE FUNCTION 
			LEAST (@str1 NVARCHAR(max), @str2 NVARCHAR(max)) 
		RETURNS
			NVARCHAR(max) AS
				BEGIN
					DECLARE @retVal NVARCHAR(max);

					SET @retVal =
						(
							SELECT 
								CASE
									WHEN
										@str1 <= @str2 
									THEN 
										@str1
									ELSE
										@str2
								END AS retVal
						)

					RETURN @retVal;
				END";

	Database::query($sql);
}

Database::commit();
return true;
?>