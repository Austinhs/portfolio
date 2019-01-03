<?php
if (empty($GLOBALS["FocusFinanceConfig"]))
	return false;

if (!$GLOBALS["FocusFinanceConfig"]["enabled"])
	return false;

if (Database::$type == "msql" && !Database::indexExists("custom_field_select_options", "custom_field_select_options_id2"))
	Database::query("CREATE INDEX custom_field_select_options_id2 ON custom_field_select_options (CAST(id AS VARCHAR))");

if (!Database::indexExists("custom_fields", "custom_fields_alias"))
	Database::query("CREATE INDEX custom_fields_alias ON custom_fields (alias)");

if (!Database::indexExists("gl_pr_staff_jobs", "gl_pr_staff_jobs_job_code_primary"))
	Database::query("CREATE INDEX gl_pr_staff_jobs_job_code_primary ON gl_pr_staff_jobs (job_code_primary)");

$rpt = DBEscapeString("
	SELECT    u.staff_id AS \"Staff ID\",
	          u.custom_556 AS \"SSN\",
	          CONCAT(u.last_name, ', ', u.first_name) AS \"Name\",
	          CASE
	             WHEN cfle.log_field1 IS NOT NULL THEN CONCAT(cfle.log_field1, '-', CAST(CAST(cfle.log_field1 AS BIGINT) + 1 AS VARCHAR))
	             ELSE 'N/A'
	          END AS \"School Year\",
	          COALESCE(cfso2.label, 'N/A') AS \"Final\",
	          COALESCE(cfso3.label, 'N/A') AS \"Eval Rating\",
	          f.name AS \"Facility Name\",
	          f.code AS \"Facility Code\"
	FROM      custom_fields cf
	JOIN      custom_field_log_entries cfle ON cfle.field_id = cf.id
	JOIN      users u ON u.staff_id = cfle.source_id
	JOIN      gl_pr_staff_jobs sj ON sj.staff_id = cfle.source_id
	JOIN      gl_pr_staff_job_positions sjp ON sjp.staff_job_id = sj.id AND CAST(sjp.fyear AS VARCHAR) = cfle.log_field1
	JOIN      gl_pr_positions p ON p.id = sjp.position_id
	JOIN      gl_facilities f ON f.id = p.facility_id
	LEFT JOIN custom_field_select_options cfso2 ON CAST(cfso2.id AS VARCHAR) = cfle.log_field2
	LEFT JOIN custom_field_select_options cfso3 ON CAST(cfso3.id AS VARCHAR) = cfle.log_field3
	WHERE     cf.alias = 'pers_eval'
	AND       cfle.source_class = 'FocusUser'
	AND       sj.job_code_primary = 'Y'
	AND       sjp.staffs_primary_position = 'Y'
	AND       cf.deleted IS NULL
	AND       cfso2.deleted IS NULL
	AND       cfso3.deleted IS NULL
	AND       u.deleted IS NULL
	AND       sj.deleted IS NULL
	ORDER BY  u.last_name, u.first_name, cfle.log_field1 DESC"
);

$res = Database::get("
	SELECT cr.id
	FROM   custom_reports cr
	JOIN   custom_reports_folders crf ON crf.id = cr.parent_id
	WHERE  cr.title = 'Contract Type and Eval'
	AND    crf.title = 'HR Reports'
	AND    crf.package = 'Finance'
	AND    crf.parent_id = -1"
);

if (empty($res))
{
	// Create the sequence if it doesn't exist
	if (!Database::sequenceExists("custom_reports_seq"))
		Database::createSequence("custom_reports_seq");

	// Reset the sequence
	$rows = Database::get("SELECT MAX(id) AS n FROM custom_reports");
	$row  = reset($rows);

	if(!empty($row))
	{
		$n   = intval($row['N']) + 1;
		$sql = "ALTER SEQUENCE custom_reports_seq RESTART WITH {$n}";

		Database::query($sql);
	}

	$res  = Database::get("SELECT id FROM custom_reports_folders WHERE title = 'HR Reports' AND package = 'Finance' AND parent_id = -1");

	if (empty($res))
	{
		$next = Database::nextSql("custom_reports_folders_seq");
		Database::query("INSERT INTO custom_reports_folders (id, parent_id, package, title) VALUES ({$next}, -1, 'Finance', 'HR Reports')");
		$res = Database::get("SELECT id FROM custom_reports_folders WHERE title = 'HR Reports' AND package = 'Finance' AND parent_id = -1");
	}

	$next = Database::nextSql("custom_reports_seq");

	Database::query("
		INSERT INTO custom_reports (id, title, query, profile_ids, multiple_queries, package, parent_id)
		VALUES      ({$next}, 'Contract Type and Eval', '{$rpt}', '||1||', 'N', 'Finance', {$res[0]["ID"]})"
	);
}
else
	Database::query("UPDATE custom_reports SET query = '{$rpt}' WHERE id = {$res[0]["ID"]}");

return true;
