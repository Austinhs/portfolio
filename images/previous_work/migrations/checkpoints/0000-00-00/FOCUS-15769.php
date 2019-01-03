<?php
// Block run of MS SQL Server code for now.
if (Database::$type === 'mssql') {
	return false;
}

// Block run if immunizations isn't installed.
if (!Database::tableExists('imm_config')) {
	return false;
}

Database::query("create or replace function fn_imm_rules_rpt (
	p_syear int
)
returns table (
	immunization varchar(50),
	ruleset varchar(50),
	step varchar(20),
	requirements varchar(1000),
	explanation varchar(1000),
	rule varchar(4000)
)
as $$
declare
	v_sql text;
	v_ruleset_group varchar(50);
	cr record;
begin

	-- Check for null pass.
	if p_syear is null then
		select cast(value as int) into p_syear from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null;
	end if;

	-- Get ruleset group.
	select
		value into v_ruleset_group
	from program_config
	where title = 'IMM_SELECTED_RULESET_GROUP'
		and program = 'system'
		and school_id is null
		and syear = p_syear;

	-- If the ruleset group isn't set for the specified year, return the ruleset group of the max set year.
	if v_ruleset_group is null then
		select
			value into v_ruleset_group
		from program_config
		where title = 'IMM_SELECTED_RULESET_GROUP'
			and school_id is null
			and syear = (select max(syear) from program_config where title = 'IMM_SELECTED_RULESET_GROUP' and school_id is null);
	end if;

	v_sql := '
		select
			cast((regexp_split_to_array(fn.title, '':''))[2] as varchar) immunization,
			cast(regexp_replace(fn.title, ''\D'', '''', ''g'') as varchar) ruleset,
			cast(fn.rule_order as varchar) step,
			cast(fn.t2comp as varchar) requirement,
			cast(case
				when fn.grade_limiter = ''Y'' then concat(''Requires grade: '', dv.dv_value)
				else fn.t3error
			end as varchar) explanation,
			cast(case
				when fn.operand is not null then concat(fn.code, '' '', fn.operand, '' '', fn.score)
				else concat(fn.code, '' >= 1'')
			end as varchar) rules
		from (
	';

	for cr in
		select distinct
			i.title,
			i.code
		from imm_immunizations i
		join imm_vaccines v
			on v.immunization_id = i.id
		join imm_ruleset_groups rg
			on rg.id = v.ruleset_group_id
		where rg.title = v_ruleset_group
		order by i.title
	loop
		v_sql := concat(v_sql, 'select * from fn_imm_get_rulesets(''', v_ruleset_group, ''', ''', cr.code, ''', ', p_syear, ') union ');
	end loop;

	v_sql := concat(substring(v_sql for length(v_sql) - 6), ') fn ');

	v_sql := concat(v_sql, ' 
		join imm_rulesets r
			on r.title = fn.title
		left join imm_rulesets_rules rr
			on rr.ruleset_id = r.id and rr.rule_order = fn.rule_order and rr.grade_limiter = ''Y'' and (
				', p_syear ,' between rr.start_dt and rr.end_dt
					or
				(', p_syear ,' >= rr.start_dt and rr.end_dt is null)
			)
		left join imm_rulesets_rules_dynamic_values rrdv
			on rrdv.rulesets_rules_id = rr.id and rrdv.value_order = 2
		left join imm_dynamic_values dv
			on dv.id = rrdv.dynamic_value_id
		order by fn.title, fn.rule_order;');

	return query execute v_sql;
end;
$$
language plpgsql;");

// Make sure the Immunizations folder exists.
$rcounter = Database::get("select count(*) as c from custom_reports_folders where title = 'Immunizations' and parent_id = -1 and package = 'SIS';");
$rcounter = $rcounter[0]['C'];
if ($rcounter == 1) {
	$rcounter = Database::get("select count(*) as c from custom_reports where title = 'Immunization Rule Report' and package = 'SIS' and parent_id = (select id from custom_reports_folders where title = 'Immunizations' and parent_id = -1 and package = 'SIS');");
	$rcounter = $rcounter[0]['C'];
	if ($rcounter == 0) {
		Database::query("insert into custom_reports (id, title, query, school_ids, profile_ids, portal_alert, multiple_queries, is_chart, package, description, parent_id) values (
			(select nextval('custom_reports_seq')),
			'Immunization Rule Report',
			'/* Immunization Rules Report
			Author: Rob Noe
			Ticket: 131455
			Date: 7/10/2018
			Reason: Create a report to display the rules used by the system.
			Requires Function: fn_imm_rules_rpt
			*/
			with rpt_data as (
				select 
					immunization,
					ruleset,
					step,
					requirements,
					explanation,
					rule
				from fn_imm_rules_rpt({SYEAR})
				union 
				select 
					immunization,
					'''',
					'''',
					'''',
					'''',
					''''
				from fn_imm_rules_rpt({SYEAR})
				order by 1, 2, 3
			)
			select
				case
					when ruleset = '''' then concat(''<b>'', immunization,''</b>'')
					else null
				end \"Immunization\",
				case
					when ruleset = '''' then null
					when immunization in (''Hep B2'', ''Hep B3'') and step = ''1'' then right(ruleset, 1)
					when step = ''1'' then ruleset
					else null
				end \"Ruleset\",
				case
					when ruleset = '''' then null
					else step
				end \"Step\",
				case
					when ruleset = '''' then null
					else requirements
				end \"Requirements\",
				case
					when ruleset = '''' then null
					else explanation
				end \"Explanation\",
				case
					when ruleset = '''' then null
					else rule
				end \"Rule\"
			from rpt_data;',
			null,
			(select concat('||', min(id), '||') from user_profiles where type = 'super'),
			'N',
			'N',
			'N',
			'SIS',
			'This report shows all of the rules used within the immunizations system.',
			(select id from custom_reports_folders where title = 'Immunizations' and parent_id = -1 and package = 'SIS'));
		");
	}
}
