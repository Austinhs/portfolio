<?php
// Block run of MS SQL Server code for now.
if (Database::$type === 'mssql') {
	return false;
}

// Block run if immunizations isn't installed.
if (!Database::tableExists('imm_config')) {
	return false;
}

// Make sure the Immunizations folder exists.
$rcounter = Database::get("select count(*) as c from custom_reports_folders where title = 'Immunizations' and parent_id = -1 and package = 'SIS';");
$rcounter = $rcounter[0]['C'];
if ($rcounter == 1) {
	$rcounter = Database::get("select count(*) as c from custom_reports where title = 'Immunization Rule Changes Report' and package = 'SIS' and parent_id = (select id from custom_reports_folders where title = 'Immunizations' and parent_id = -1 and package = 'SIS');");
	$rcounter = $rcounter[0]['C'];
	if ($rcounter == 0) {
		Database::query("insert into custom_reports (id, title, query, school_ids, profile_ids, portal_alert, multiple_queries, is_chart, package, description, parent_id) values (
			(select nextval('custom_reports_seq')),
			'Immunization Rule Changes Report',
			'/* Immunization Rule Changes Report
			Author: Rob Noe
			Ticket: 131456
			Date: 7/11/2018
			Reason: Create a report to display the changes to the rule system.
			*/
			with vars as (
				select
					{SYEAR} syear,
					(select
						value 
					from program_config
					where title = ''IMM_SELECTED_RULESET_GROUP''
						and school_id is null
						and syear = (select max(syear) from program_config where title = ''IMM_SELECTED_RULESET_GROUP'' and school_id is null)
					) ruleset
			),
			immunizations as (
				select distinct
					i.title,
					i.code
				from imm_immunizations i
				join imm_ruleset_groups_rulesets rgr
					on rgr.immunization_id = i.id
				join imm_ruleset_groups rg
					on rg.id = rgr.ruleset_group_id
				where rg.title = (select ruleset from vars)
			),
			new_data as (
				select
					concat((regexp_split_to_array(r_new.title, '':''))[2], '' Ruleset '', regexp_replace(fn_new.title, ''\D'', '''', ''g''), '' Step '', fn_new.rule_order) ruleset_nm,
					fn_new.t2comp requirement,
					case
						when fn_new.grade_limiter = ''Y'' then concat(''Requires grade: '', dv_new.dv_value)
						else fn_new.t3error
					end explanation,
					case
						when fn_new.operand is not null then concat(fn_new.code, '' '', fn_new.operand, '' '', fn_new.score)
						else concat(fn_new.code, '' >= 1'')
					end rule_code,
					fn_new.*
				from immunizations i
				cross join lateral fn_imm_get_rulesets((select ruleset from vars), i.code, (select syear from vars)) fn_new
				join imm_rulesets r_new
					on r_new.title = fn_new.title
				left join imm_rulesets_rules rr_new
					on rr_new.ruleset_id = r_new.id and rr_new.rule_order = fn_new.rule_order and rr_new.grade_limiter = ''Y'' and (
						(select syear from vars) between rr_new.start_dt and rr_new.end_dt
							or
						((select syear from vars) >= rr_new.start_dt and rr_new.end_dt is null)
					)
				left join imm_rulesets_rules_dynamic_values rrdv_new
					on rrdv_new.rulesets_rules_id = rr_new.id and rrdv_new.value_order = 2
				left join imm_dynamic_values dv_new
					on dv_new.id = rrdv_new.dynamic_value_id
			),
			old_data as (
				select
					concat((regexp_split_to_array(r_old.title, '':''))[2], '' Ruleset '', regexp_replace(fn_old.title, ''\D'', '''', ''g''), '' Step '', fn_old.rule_order) ruleset_nm,
					fn_old.t2comp requirement,
					case
						when fn_old.grade_limiter = ''Y'' then concat(''Requires grade: '', dv_old.dv_value)
						else fn_old.t3error
					end explanation,
					case
						when fn_old.operand is not null then concat(fn_old.code, '' '', fn_old.operand, '' '', fn_old.score)
						else concat(fn_old.code, '' >= 1'')
					end rule_code,
					fn_old.*
				from immunizations i
				cross join lateral fn_imm_get_rulesets((select ruleset from vars), i.code, (select syear - 1 from vars)) fn_old
				join imm_rulesets r_old
					on r_old.title = fn_old.title
				left join imm_rulesets_rules rr_old
					on rr_old.ruleset_id = r_old.id and rr_old.rule_order = fn_old.rule_order and rr_old.grade_limiter = ''Y'' and (
						(select syear - 1 from vars) between rr_old.start_dt and rr_old.end_dt
							or
						((select syear - 1 from vars) >= rr_old.start_dt and rr_old.end_dt is null)
					)
				left join imm_rulesets_rules_dynamic_values rrdv_old
					on rrdv_old.rulesets_rules_id = rr_old.id and rrdv_old.value_order = 2
				left join imm_dynamic_values dv_old
					on dv_old.id = rrdv_old.dynamic_value_id
			),
			updates as (
				select
					cast(''Updated'' as varchar) \"Modification\",
					nd.ruleset_nm \"Ruleset\",
					nd.requirement \"New Requirement\",
					od.requirement \"Old Requirement\",
					nd.explanation \"New Explanation\",
					od.explanation \"Old Explanation\",
					nd.rule_code \"New Code\",
					od.rule_code \"Old Code\"
				from new_data nd
				join old_data od
					on od.ruleset_nm = nd.ruleset_nm and od.rule_order = nd.rule_order
				where nd.rule_code <> od.rule_code
			),
			adds as (
				select
					cast(''Added'' as varchar) \"Modification\",
					nd.ruleset_nm \"Ruleset\",
					nd.requirement \"New Requirement\",
					cast(null as varchar) \"Old Requirement\",
					nd.explanation \"New Explanation\",
					cast(null as varchar) \"Old Explanation\",
					nd.rule_code \"New Code\",
					cast(null as varchar) \"Old Code\"
				from new_data nd
				left join old_data od
					on od.ruleset_nm = nd.ruleset_nm and od.rule_order = nd.rule_order
				where od.ruleset_nm is null
			),
			deletes as (
				select
					cast(''Removed'' as varchar) \"Modification\",
					od.ruleset_nm \"Ruleset\",
					cast(null as varchar) \"New Requirement\",
					nd.requirement \"Old Requirement\",
					cast(null as varchar) \"New Explanation\",
					nd.explanation \"Old Explanation\",
					cast(null as varchar) \"New Code\",
					nd.rule_code \"Old Code\"
				from old_data od
				left join new_data nd
					on nd.ruleset_nm = od.ruleset_nm and nd.rule_order = od.rule_order
				where nd.ruleset_nm is null
			)
			select * from updates
			union
			select * from adds
			union
			select * from deletes;',
			null,
			(select concat('||', min(id), '||') from user_profiles where type = 'super'),
			'N',
			'N',
			'N',
			'SIS',
			'This report shows rule changes within the immunization system.',
			(select id from custom_reports_folders where title = 'Immunizations' and parent_id = -1 and package = 'SIS'));
		");
	}
}