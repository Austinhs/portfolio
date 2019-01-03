<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"])
{
	echo("Unable to find an active Finance configuration");
	return false;
}

Database::begin();

//Data for custom fields - adding to Employee Summary category
$g_cat = CustomFieldCategory::getOne("source_class = 'FocusUser' AND title = 'General'");

if (!$g_cat)
	$g_cat = (new CustomFieldCategory())
		->setTitle("General")
		->setSourceClass("FocusUser")
		->setSortOrder(1)
		->setLegacyId(1)
		->setDefaultProfilesView([ "1" ])
		->setDefaultProfilesEdit([ "1" ])
		->setSis(1)
		->setErp(1)
		->persist();

$enc1     = (Database::$type == "postgres" ? "\"" : "[");
$enc2     = (Database::$type == "postgres" ? "\"" : "]");
$g_cat_id = $g_cat->getID();
$g_fields = [
	[
		'db'             => 'all',
		'title'          => 'Last, First M',
		'column'         => 'full_name',
		'type'           => 'computed',
		"computed_query" => "
			SELECT staff_id,
			       LTRIM(RTRIM(UPPER(CONCAT(COALESCE(last_name, ' '), ', ', COALESCE(first_name, ' '), ' ', LEFT(COALESCE(middle_name, ' '), 1))))) AS value
			FROM   users
			WHERE  deleted IS NULL"
	],
	[
		'db'             => 'all',
		'title'          => 'Last, Nickname',
		'column'         => 'full_nickname',
		'type'           => 'computed',
		"computed_query" => "
			SELECT staff_id,
			       LTRIM(RTRIM(UPPER(CONCAT(COALESCE(last_name, ' '), ', ', COALESCE(first_name, ' '))))) AS value
			FROM   users
			WHERE  deleted IS NULL"
	],
	[
		'db'             => 'all',
		'title'          => 'First Init',
		'column'         => 'first_init',
		'type'           => 'computed',
		"computed_query" => "
			SELECT staff_id,
			       LTRIM(RTRIM(UPPER(CONCAT(COALESCE(first_name, ' '), ' ', LEFT(COALESCE(last_name, ' '), 1))))) AS value
			FROM   users
			WHERE  deleted IS NULL"
	],
	[
		'db'             => 'postgres',
		'title'          => 'Pay Type',
		'column'         => 'pay_type',
		'type'           => 'computed',
		"computed_query" => "
			SELECT   DISTINCT ON (u.staff_id)
			         u.staff_id,
			         LTRIM(RTRIM(UPPER(CONCAT(COALESCE(pt.pay_type, ' '), ' ', COALESCE(pt.title, ' '))))) AS value
			FROM     users u
			JOIN     gl_pr_staff_jobs sj ON sj.staff_id = u.staff_id
			JOIN     gl_pr_staff_job_positions sjp ON sjp.staff_job_id = sj.id
			JOIN     gl_pr_positions p ON p.id = sjp.position_id
			JOIN     gl_pr_pay_types pt ON pt.id = p.pay_type_id
			WHERE    u.deleted IS NULL
			AND      sj.deleted IS NULL
			AND      sjp.fyear = {syear}
			AND      {date} BETWEEN COALESCE(sj.starting_date, '1970-01-01') AND COALESCE(sj.termination_date, '2099-12-31')
			AND      {date} BETWEEN COALESCE(sjp.date_starting, '1970-01-01') AND COALESCE(sjp.date_ending, '2099-12-31')
			AND      {date} BETWEEN COALESCE(p.date_starting, '1970-01-01') AND COALESCE(p.date_ending, '2099-12-31')
			ORDER BY u.staff_id, sjp.staffs_primary_position DESC, sj.job_code_primary DESC, COALESCE(p.date_starting, '1970-01-01') DESC,
			         COALESCE(sjp.date_starting, '1970-01-01') DESC, COALESCE(sj.starting_date, '1970-01-01') DESC, sj.staff_job_index"
	],
	[
		'db'             => 'postgres',
		'title'          => 'Job Code',
		'column'         => 'job_code',
		'type'           => 'computed',
		"computed_query" => "
			SELECT   DISTINCT ON (u.staff_id)
			         u.staff_id,
			         LTRIM(RTRIM(UPPER(CONCAT(COALESCE(j.job_number, ' '), ' ', COALESCE(j.title, ' '))))) AS value
			FROM     users u
			JOIN     gl_pr_staff_jobs sj ON sj.staff_id = u.staff_id
			JOIN     gl_pr_staff_job_positions sjp ON sjp.staff_job_id = sj.id
			JOIN     gl_pr_jobs_local j ON j.id = sjp.job_id
			WHERE    u.deleted IS NULL
			AND      sj.deleted IS NULL
			AND      sjp.fyear = {syear}
			AND      {date} BETWEEN COALESCE(sj.starting_date, '1970-01-01') AND COALESCE(sj.termination_date, '2099-12-31')
			AND      {date} BETWEEN COALESCE(sjp.date_starting, '1970-01-01') AND COALESCE(sjp.date_ending, '2099-12-31')
			ORDER BY u.staff_id, sjp.staffs_primary_position DESC, sj.job_code_primary DESC, COALESCE(sjp.date_starting, '1970-01-01') DESC,
			         COALESCE(sj.starting_date, '1970-01-01') DESC, sj.staff_job_index"
	],
	[
		'db'             => 'mssql',
		'title'          => 'Pay Type',
		'column'         => 'pay_type',
		'type'           => 'computed',
		"computed_query" => "
			SELECT   u.staff_id,
			         MIN(LTRIM(RTRIM(UPPER(CONCAT(COALESCE(pt.pay_type, ' '), ' ', COALESCE(pt.title, ' ')))))) AS value
			FROM     users u
			JOIN     gl_pr_staff_jobs sj ON sj.staff_id = u.staff_id
			JOIN     gl_pr_staff_job_positions sjp ON sjp.staff_job_id = sj.id
			JOIN     gl_pr_positions p ON p.id = sjp.position_id
			JOIN     gl_pr_pay_types pt ON pt.id = p.pay_type_id
			WHERE    u.deleted IS NULL
			AND      sj.deleted IS NULL
			AND      sjp.fyear = {syear}
			AND      {date} BETWEEN COALESCE(sj.starting_date, '1970-01-01') AND COALESCE(sj.termination_date, '2099-12-31')
			AND      {date} BETWEEN COALESCE(sjp.date_starting, '1970-01-01') AND COALESCE(sjp.date_ending, '2099-12-31')
			AND      {date} BETWEEN COALESCE(p.date_starting, '1970-01-01') AND COALESCE(p.date_ending, '2099-12-31')
			GROUP BY u.staff_id"
	],
	[
		'db'             => 'mssql',
		'title'          => 'Job Code',
		'column'         => 'job_code',
		'type'           => 'computed',
		"computed_query" => "
			SELECT   u.staff_id,
			         MIN(LTRIM(RTRIM(UPPER(CONCAT(COALESCE(j.job_number, ' '), ' ', COALESCE(j.title, ' ')))))) AS value
			FROM     users u
			JOIN     gl_pr_staff_jobs sj ON sj.staff_id = u.staff_id
			JOIN     gl_pr_staff_job_positions sjp ON sjp.staff_job_id = sj.id
			JOIN     gl_pr_jobs_local j ON j.id = sjp.job_id
			WHERE    u.deleted IS NULL
			AND      sj.deleted IS NULL
			AND      sjp.fyear = {syear}
			AND      {date} BETWEEN COALESCE(sj.starting_date, '1970-01-01') AND COALESCE(sj.termination_date, '2099-12-31')
			AND      {date} BETWEEN COALESCE(sjp.date_starting, '1970-01-01') AND COALESCE(sjp.date_ending, '2099-12-31')
			GROUP BY u.staff_id"
	],
	[
		'db'             => 'all',
		'title'          => 'Contracted Salary amount',
		'column'         => 'contracted',
		'type'           => 'computed',
		"computed_query" => "
			SELECT   u.staff_id,
			         SUM(w.contract_wages) AS value
			FROM     users u
			JOIN     gl_pr_current_fyear_job_wages w ON w.staff_id = u.staff_id
			WHERE    u.deleted IS NULL
			AND      w.deleted IS NULL
			AND      w.fyear = {syear}
			AND      w.supplement = 'N'
			GROUP BY u.staff_id"
	],
];

$access_profiles = Database::get("SELECT profile_id FROM permission WHERE {$enc1}key{$enc2} = 'hr::demographic'");

foreach ($g_fields as $sort_increment => $field)
{
	if ($field["db"] == "all" || $field["db"] == Database::$type)
	{
		$cf = CustomField::getOne("alias = '{$field["column"]}' AND source_class = 'FocusUser'");

		if ($cf)
		{
			echo("<DIV>Found an existing custom field ({$field["column"]})</DIV>");

			if ($cf->getComputedQuery() != $field['computed_query'])
			{
				echo("<DIV STYLE=\"padding-left:3em\">Updating</DIV>");
				$cf->setComputedQuery($field['computed_query'])->persist();
			}
			else
				echo("<DIV STYLE=\"padding-left:3em\">Skipping (no change)</DIV>");
		}
		else
		{
			echo("<DIV>Adding a new custom field ({$field["column"]})</DIV>");

			$cf = (new CustomField())
				->setTitle($field['title'])
				->setAlias($field['column'])
				->setType($field['type'])
				->setComputedQuery($field['computed_query'])
				->setSourceClass('FocusUser')
				->setRequiresAuthentication(1)
				->persist();
		}

		$field_id = $cf->getId();

		foreach ($access_profiles as $access_profile)
		{
			$res = Database::get("
				SELECT 'x'
				FROM   permission
				WHERE  profile_id = {$access_profile['PROFILE_ID']}
				AND    {$enc1}key{$enc2} = 'FocusUser:{$field_id}:can_view'"
			);

			if (empty($res))
				Database::query("INSERT INTO permission (profile_id, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'FocusUser:{$field_id}:can_view')");

			$res = Database::get("
				SELECT 'x'
				FROM   permission
				WHERE  profile_id = {$access_profile['PROFILE_ID']}
				AND    {$enc1}key{$enc2} = 'FocusUser:{$field_id}:can_edit'"
			);

			if (empty($res))
				Database::query("INSERT INTO permission (profile_id, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'FocusUser:{$field_id}:can_edit')");
		}

		//Join to category - add to the bottom
		$cfjc = CustomFieldJoinCategory::getOne("category_id = {$g_cat_id} AND field_id = {$field_id}");

		if (!$cfjc)
			$cfjc = (new CustomFieldJoinCategory())
				->setCategoryId($g_cat_id)
				->setFieldId($field_id)
				->setSortOrder($sort_increment + 1)
				->persist();
	}
}

$cfjc = new CustomFieldJoinCategory();
$cfjc->fixSortOrders();

Database::query("
	UPDATE custom_fields
	SET    computed_query = REPLACE(computed_query, '\"Contact Info\"', '\"Info\"')
	WHERE  source_class = 'FocusUser'
	AND    alias = 'es_contact'"
);
Database::query("
	UPDATE custom_fields
	SET    computed_query = REPLACE(computed_query, '\"Job Title\"', '\"Title\"')
	WHERE  source_class = 'FocusUser'
	AND    alias = 'es_active_pos'"
);
Database::query("
	UPDATE custom_fields
	SET    computed_query = REPLACE(computed_query, '\"Hours Per Day\"', '\"Hours\"')
	WHERE  source_class = 'FocusUser'
	AND    alias = 'es_active_pos'"
);
Database::query("
	UPDATE custom_fields
	SET    computed_query = CONCAT(computed_query, ' AND sj.deleted IS NULL')
	WHERE  source_class = 'FocusUser'
	AND    alias = 'es_active_pos'
	AND    computed_query NOT LIKE '%AND sj.deleted IS NULL'"
);
Database::query("
	UPDATE custom_fields
	SET    computed_query = REPLACE(computed_query, '\"Fiscal Year\"', '\"Year\"')
	WHERE  source_class = 'FocusUser'
	AND    alias = 'es_salary'"
);
Database::query("
	UPDATE custom_fields
	SET    computed_query = CONCAT(computed_query, ' AND sj.deleted IS NULL')
	WHERE  source_class = 'FocusUser'
	AND    alias = 'es_salary'
	AND    computed_query NOT LIKE '%AND sj.deleted IS NULL'"
);
Database::query("
	UPDATE custom_fields
	SET    computed_query = REPLACE(computed_query, '\"Fiscal Year\"', '\"Year\"')
	WHERE  source_class = 'FocusUser'
	AND    alias = 'es_supplements'"
);
Database::query("
	UPDATE custom_fields
	SET    computed_query = CONCAT(computed_query, ' AND sjs.deleted IS NULL AND sj.deleted IS NULL')
	WHERE  source_class = 'FocusUser'
	AND    alias = 'es_supplements'
	AND    computed_query NOT LIKE '%IS NULL'"
);

Database::commit();

return true;
