<?php
// Tags: Formbuilder
if (!Database::tableExists('sss_forms') || !Database::tableExists('sss_form_instances')) {
	return false;
}

$form_id = Database::get("SELECT id FROM sss_forms WHERE name='Injury Report'");
if (empty($form_id)) {
	return true;
}

$form_id = $form_id[0]['ID'];

$instances = Database::get("SELECT id FROM sss_form_instances WHERE form_id = {$form_id} AND raw_data IS NOT NULL");
$instances = array_column($instances, 'ID');

foreach ($instances as $instance_id) {
	$form = Database::get("SELECT raw_data FROM sss_form_instances WHERE id = {$instance_id}");
	$form = json_decode($form[0]['RAW_DATA'], true);
	unset($form['server']);

	foreach ($form['components'] as &$component) {
		if ($component['fieldName'] === 'staff_id') {
			$component['options']    = [];
			$component['optionType'] = 'sql';
			$component['sourceSQL']  = 'employees';
		}

		if ($component['type'] === 'form') {
			unset($component['formCode']);
		}
	}
	unset($component);

	$params = [
		'form' => json_encode($form)
	];


	Database::query("UPDATE sss_form_instances SET raw_data = :form WHERE id = {$instance_id}", $params);
}

Database::query("INSERT INTO sss_form_collections (form_id, name, sql) VALUES (
	{$form_id},
	'employees',
	'select concat(d.last_name, '', '', d.first_name) as text,u.staff_id as value,l.title as occupation,
			concat(a.address1,'' '',coalesce(a.address2)) as address,
			a.city, a.state, a.zipcode, u.staff_id,
			u.custom_556 as SSN,
			to_char(d.birth_date,''mm/dd/yyyy'') as birth_date,
			(case when d.gender like ''M%'' then ''[0]'' else ''[1]'' end) as gender,
			(
				select c.cont_data
				from gl_contact c,gl_contact_types ct
				where ct.id=c.cont_type
					and c.parent_class=''ERPUser''
					and c.parent_id=d.staff_id and lower(ct.title) like ''%phone%'' limit 1
			) as phone,
			(
				SELECT round(w.contract_hourly_rate,2)
				FROM gl_pr_current_fyear_job_wages w
				where w.staff_id=d.staff_id and w.deleted is null
				order by date_start desc
				limit 1
			) as rate,
			(
				SELECT round(w.contract_hours_per_day,1)
				FROM gl_pr_current_fyear_job_wages w
				WHERE  w.staff_id=d.staff_id and w.deleted is null
				ORDER BY date_start desc
				limit 1
			) as HoursPerDay,
			''[0]'' AS RateUnits,
			(
				SELECT jl.title
				FROM GL_PR_JOBS_local jl
				WHERE jl.id=p.job_id
			) AS occupation,
			to_char(d.continuous_employment_date,''mm/dd/yyyy'') as dateEmployed
		FROM gl_hr_demographic d, gl_pr_staff_job_positions p, gl_pr_jobs_local l, users u
		LEFT OUTER JOIN gl_address a ON (a.parent_id=u.staff_id and a.parent_class=''ERPUser'')
		WHERE d.staff_id=u.staff_id
			and d.staff_id = p.staff_id
			and p.staffs_primary_position=''Y''
			and l.id=p.job_id
			and p.fyear = ''{fyear}''
	'
)");
