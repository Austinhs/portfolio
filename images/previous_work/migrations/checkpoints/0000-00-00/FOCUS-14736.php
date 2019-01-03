<?php
// Block run of MS SQL Server code for now.
if (Database::$type === 'mssql') {
	return false;
}

// Make sure FOCUS-14735 has run so that changes can be made.
Migrations::depend('FOCUS-14735');

// Block run if immunizations isn't installed.
if (!Database::tableExists('imm_config')) {
	return false;
}

// Make sure the Immunizations folder exists.
$rcounter = Database::get("select count(*) as c from custom_reports_folders where title = 'Immunizations' and parent_id = -1 and package = 'SIS';");
$rcounter = $rcounter[0]['C'];
if ($rcounter == 1) {
	$rcounter = Database::get("select count(*) as c from custom_reports where title = 'School Compliance Report' and package = 'SIS' and parent_id = (select id from custom_reports_folders where title = 'Immunizations' and parent_id = -1 and package = 'SIS');");
	$rcounter = $rcounter[0]['C'];
	if ($rcounter == 0) {
		Database::query("insert into custom_reports (id, title, query, school_ids, profile_ids, portal_alert, multiple_queries, is_chart, package, description, parent_id) values (
			(select nextval('custom_reports_seq')),
			'School Compliance Report',
			'/* Immunization School Compliance Report
			Author: Rob Noe
			Ticket:
				JIRA: 14736
			Date: 02/23/2018
			Reason: Create a report to display school immunization compliance issues.
			Requires Variables: imm_type.sql
			*/
			with vars as (
				select {SYEAR} as syear,
				{SCHOOL_ID} as school_id
			),
			imm as (
				select
					(select cast(id as int) from custom_fields where column_name = (select code from imm_config where title = ''IMM_CUSTOM_COL_NAME'')) comp_field_id,
					(select id from imm_ruleset_groups where title = (select value from program_config where syear = (select syear from vars) and school_id is null and program = ''system'' and title = ''IMM_SELECTED_RULESET_GROUP'')) ruleset_group_id
			)
			select
				cfle.source_id as \"Student ID\",
				concat(s.last_name, '', '', s.first_name) as \"Student Name\",
				cast(s.custom_200000004 as date) as \"DOB\",
				sg.short_name as \"Grade\",
				cfso.code as \"Status\",
				i.title as \"Immunization\",
				cfle.log_field3 as \"Error\"
			from custom_field_log_entries cfle
			join student_enrollment se
				on se.student_id = cfle.source_id and se.syear = (select syear from vars)
			join students s
				on s.student_id = cfle.source_id
			join schools sc
				on sc.id = se.school_id
			join school_gradelevels sg
				on sg.id = se.grade_id
			left join custom_field_select_options cfso
				on cfso.id = s.custom_630
			join imm_immunizations i
				on cast(i.id as varchar) = cfle.log_field1
			where cfle.log_field2 = ''N''
				and cfle.field_id = (select comp_field_id from imm)
				and coalesce(se.custom_9, ''N'') != ''Y''
				and (se.end_date is null or current_date between se.start_date and se.end_date)
				and se.school_id = (select school_id from vars)
				{IMM_TYPE}
			order by sc.custom_327, cast(replace(replace(sg.short_name, ''KG'', ''0.75''), ''PK'', ''0.5'') as float), concat(s.last_name, '', '', s.first_name), i.title;',
			null,
			(select concat('||', min(id), '||') from user_profiles where type = 'super'),
			'N',
			'N',
			'N',
			'SIS',
			'This report shows all students who are currently out of compliance for the school based on the immunization compliance rules.',
			(select id from custom_reports_folders where title = 'Immunizations' and parent_id = -1 and package = 'SIS'));
		");
	}
}
