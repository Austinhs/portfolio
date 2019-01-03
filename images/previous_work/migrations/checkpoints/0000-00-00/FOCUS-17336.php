<?php

if (!Database::tableExists('imm_ruleset_groups_rulesets') || !Database::tableExists('imm_rulesets_rules')){
	return false;
}

if (Database::$type === 'mssql') {
		return false;
}

	Database::query("
	create or replace function fn_imm_calc_clean(
		p_student_id bigint default null,
		p_syear int default null
	)
		returns text
	as $$
	
	declare
		v_imm_col_id int;
		v_selected_ruleset_group_id int;
		cr record;
	begin
	
		-- Select current syear to calculate against.
		if p_syear is null then
			select cast(value as int) into p_syear from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null;
		end if;
	
		-- Catch to ensure the Dynamic Version for Immunizations system has all required variables set.
		if (select
				count(*)
			from program_config
			where title in (
					'IMM_COMPLIANCE',
					'IMM_SELECTED_RULESET_GROUP',
					'IMM_ERROR_HANDLING'/*, Turning this one off coalesce added by BH 17SEPT18
					'IMM_INTERVAL_DAYS'*/
				)
				and syear = (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null)
				and program = 'system'
				and school_id is null
				and value is null) > 0
		then
			return null;
		end if;
	
		-- Check imm_config as well.
		if (select
				count(*)
			from imm_config
			where code is null) > 0
		then
			return null;
		end if;
	
		-- Ensure Immunization Module is active.
		if (select value from program_config where program = 'system' and title = 'IMM_COMPLIANCE' and syear = (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null) and school_id is null) <> 'Y' then
			return null;
		end if;
	
		-- Pull the column id for the custom_field to update.
		select id into v_imm_col_id from custom_fields where column_name = (select code from imm_config where title = 'IMM_CUSTOM_COL_NAME');
	
		-- Pull the selected_ruleset_group from config table.
		select id into v_selected_ruleset_group_id from imm_ruleset_groups where title = (select value from program_config where program = 'system' and title = 'IMM_SELECTED_RULESET_GROUP' and syear = (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null) and school_id is null);
	
		if v_imm_col_id is null or v_selected_ruleset_group_id is null then
			return null;
		end if;
	
		if p_student_id is null then
			for cr in
				select
					cfle.id,
					cfle.source_id,
					cfle.log_field1,
					i.code,
					rg.immunization_id
				from custom_field_log_entries cfle
				join imm_immunizations i
					on cast(i.id as varchar) = cfle.log_field1
				left join (
					select distinct
						rgr.immunization_id
					from imm_ruleset_groups_rulesets rgr
					where rgr.ruleset_group_id = v_selected_ruleset_group_id
						and p_syear >= rgr.start_dt
						and (p_syear <= rgr.end_dt or rgr.end_dt is null)) rg
					on rg.immunization_id = i.id
				where cfle.field_id = v_imm_col_id
					and rg.immunization_id is null
			loop
				delete from custom_field_log_entries where id = cr.id and source_id = cr.source_id and source_class = 'SISStudent' and field_id = v_imm_col_id;
			end loop;
		else
			for cr in
				select
					cfle.id,
					cfle.source_id,
					cfle.log_field1,
					i.code,
					rg.immunization_id
				from custom_field_log_entries cfle
				join imm_immunizations i
					on cast(i.id as varchar) = cfle.log_field1
				left join (
					select distinct
						rgr.immunization_id
					from imm_ruleset_groups_rulesets rgr
					where rgr.ruleset_group_id = v_selected_ruleset_group_id
						and p_syear >= rgr.start_dt
						and (p_syear <= rgr.end_dt or rgr.end_dt is null)) rg
					on rg.immunization_id = i.id
				where cfle.field_id = v_imm_col_id
					and cfle.source_id = p_student_id
					and rg.immunization_id is null
			loop
				delete from custom_field_log_entries where id = cr.id and source_id = cr.source_id and source_class = 'SISStudent' and field_id = v_imm_col_id;
			end loop;
		end if;
	
		return null;
	
	end;
	$$ language plpgsql;	
	");

	Database::query("
	create or replace function fn_imm_calc_data (
		p_student_id bigint default null,
		p_syear int default null
	)
	returns table (
		student_id bigint,
		gradelevel varchar(5),
		dob date,
		days_interval int,
		age int,
		age_in_months int,
		state_code varchar(10),
		vaccine_class varchar(20),
		vaccine_id int,
		shot_date date,
		vac_dose_num int,
		class_dose_num int,
		vac_lag_time_days int,
		class_lag_time_days int
	)
	as $$
	declare
		v_sql text;
		v_shot_dates varchar;
		v_selected_ruleset_group_id int;
		v_student_id varchar;
	begin
	
		if p_syear is null then
			select cast(value as int) into p_syear from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null;
		end if;
	
		select code into v_shot_dates from imm_config where title = 'IMM_DATA_COLUMNS';
	
		select id into v_selected_ruleset_group_id from imm_ruleset_groups where title = (select value from program_config where program = 'system' and title = 'IMM_SELECTED_RULESET_GROUP' and syear = (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null) and school_id is null);
	
		if p_student_id is null then
			v_student_id := 'null';
		else
			v_student_id := p_student_id;
		end if;
	
		v_sql := concat('with vars as (
			select
				(select ', p_syear, ') syear,
				(select ', v_selected_ruleset_group_id, ') vruleset_group_id
		),
		student_info as (
			select
				se.student_id,
				se.gradelevel,
				cast(s.custom_200000004 as date) dob,
				date_part(''year'', age(s.custom_200000004)) age
			from (
				select
					se.end_date,
					se.student_id,
					sg.short_name gradelevel,
					row_number() over (partition by se.student_id order by se.end_date desc) rn
				from student_enrollment se
				join school_gradelevels sg
					on sg.id = se.grade_id
				where se.syear = (select syear from vars)
					and (se.student_id = ', v_student_id, ' or coalesce(', v_student_id, ', 1) = 1)
				group by se.end_date, se.student_id, sg.short_name) se
			join students s
				on s.student_id = se.student_id
			where se.rn = 1
		),
		cjoin_data as (
			select
				si.student_id,
				si.gradelevel,
				si.dob,
				si.age,
				i.id,
				i.code,
				i.title as label
			from student_info si
			cross join imm_immunizations i
			where i.id in (
				select distinct
					immunization_id
				from imm_ruleset_groups_rulesets
				where ruleset_group_id = (select vruleset_group_id from vars)
					and (select syear from vars) >= start_dt
					and ((select syear from vars) <= end_dt or end_dt is null)
			)
		),
		shot_info as (
			select distinct
				x.cfso_id,
				x.student_id,
				x.title,
				x.code,
				x.vaccine_id,
				rank() over (partition by x.student_id, x.title, x.vaccine_id order by cast(x.shot_dates as date)) vac_dose_num,
				rank() over (partition by x.student_id, x.title order by cast(x.shot_dates as date)) class_dose_num,
				cast(x.shot_dates as date) shot_date
			from (
			select
				cfle.id cfle_id,
				cfso.id cfso_id,
				cfle.source_id student_id,
				cfso.code,
				i.title,
				cfso.id vaccine_id,
				unnest(array_remove(array[', v_shot_dates, '], null)) shot_dates
			from custom_field_log_entries cfle
			join custom_field_select_options cfso
				on cast(cfso.id as varchar) = cfle.log_field1
			join imm_vaccines iv
				on lower(iv.code) = lower(cfso.code)
			join imm_immunizations i
				on i.id = iv.immunization_id
			where cfle.source_class = ''SISStudent''
				and cfle.field_id = (select cast(code as int) from imm_config where title = ''IMM_SHOTS_FIELD_ID'')
				and (cfle.source_id = ', v_student_id, ' or coalesce(', v_student_id, ', 1) = 1)
				and iv.ruleset_group_id = (select vruleset_group_id from vars)
			) x
			join students s
				on s.student_id = x.student_id
			where is_date(x.shot_dates) = true
				and cast(x.shot_dates as date) >= cast(s.custom_200000004 as date)
		)
		select
			cast(cd.student_id as bigint) student_id,
			cd.gradelevel,
			cd.dob,
			/*Added coalesce to below to fix default days BH 17SEPT18*/
			(select coalesce(cast(value as int),0) from program_config where program = ''system'' and title = ''IMM_INTERVAL_DAYS'' and syear = (select syear from vars) and school_id is null) days_interval,
			cast(cd.age as integer) age,
			cast(floor(cast(abs(cd.dob - si.shot_date) as float) / cast(30.27 as float)) as integer) age_in_months,
			si.code state_code,
			i.title vaccine_class,
			cast(si.cfso_id as integer) vaccine_id,
			si.shot_date,
			cast(si.vac_dose_num as integer) vac_dose_num,
			cast(si.class_dose_num as integer) class_dose_num,
			case when si.vac_dose_num = 1 then cast(abs(cd.dob - si.shot_date) as integer)
				else cast(abs((lag(si.shot_date, 1) over (partition by si.student_id, i.title, si.vaccine_id order by si.vac_dose_num)) - si.shot_date) as integer)
			end vac_lag_time_days,
			case when si.class_dose_num = 1 then cast(abs(cd.dob - si.shot_date) as integer)
				else cast(abs((lag(si.shot_date, 1) over (partition by si.student_id, i.title order by si.class_dose_num)) - si.shot_date) as integer)
			end class_lag_time_days
		from cjoin_data cd
		join imm_immunizations i
			on lower(i.code) = lower(cd.code)
		left join shot_info si
			on si.student_id = cd.student_id and si.title = i.title
		where si.shot_date >= cd.dob or si.shot_date is null');
	
		return query execute v_sql;
	
	end;
	$$
	language plpgsql;	
	");

	Database::query("
	create or replace function fn_imm_calc(
		p_immunization varchar,
		p_student_id bigint default null,
		p_syear int default null,
		p_debug int default 0
	)
		returns text
	as $$
	
	declare
		v_imm_id int;
		v_imm_col_id int;
		v_selected_ruleset_group_id int;
		v_offset_days int;
		v_error_handling varchar;
		v_added_filter varchar := chr(32);
		v_sql text;
		v_rs_counter int;
		v_rs_selector int;
		cr record;
	begin
	
		-- Select current syear to calculate against.
		if p_syear is null then
			select cast(value as int) into p_syear from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null;
		end if;
	
		-- Catch to ensure the Dynamic Version for Immunizations system has all required variables set.
		if (select
				count(*)
			from program_config
			where title in (
					'IMM_COMPLIANCE',
					'IMM_SELECTED_RULESET_GROUP',
					'IMM_ERROR_HANDLING'/*, Coalesce added to functions turning this one off -BH 17SEPT18
					'IMM_INTERVAL_DAYS'*/
				)
				and syear = (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null)
				and program = 'system'
				and school_id is null
				and value is null) > 0
		then
			return null;
		end if;
	
		-- Check imm_config as well.
		if (select
				count(*)
			from imm_config
			where code is null) > 0
		then
			return null;
		end if;
	
		-- Ensure Immunization Module is active.
		if (select value from program_config where program = 'system' and title = 'IMM_COMPLIANCE' and syear = (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null) and school_id is null) <> 'Y' then
			if p_debug <> 1 then
				return null;
			else
				-- Return message to turn on Immunization Compliance.
				return 'Requires Immunization Compliance to be enabled within System Preferences.';
			end if;
		end if;
	
		-- Ensure an immunization was selected to run against.
		if p_immunization is null then
			return null;
		else
			-- Pull the immunization_id.
			select id into v_imm_id from imm_immunizations where code = p_immunization;
		end if;
	
		-- Ensure an immunization was pulled.
		if v_imm_id is null then
			return null;
		end if;
	
		-- Pull the column id for the custom_field to update.
		select id into v_imm_col_id from custom_fields where column_name = (select code from imm_config where title = 'IMM_CUSTOM_COL_NAME');
	
		-- Pull the selected_ruleset_group from config table.
		select id into v_selected_ruleset_group_id from imm_ruleset_groups where title = (select value from program_config where program = 'system' and title = 'IMM_SELECTED_RULESET_GROUP' and syear = (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null) and school_id is null);
	
		/*Pull the offset_days. BH added coalesce 17SEPT18*/
		select coalesce(cast(value as int),0) into v_offset_days from program_config where program = 'system' and title = 'IMM_INTERVAL_DAYS' and syear = (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null) and school_id is null;
	
		-- Pull error handling method.
		select value into v_error_handling from program_config where program = 'system' and title = 'IMM_ERROR_HANDLING' and syear = (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null) and school_id is null;
	
		-- Batch override Itemized run. This is being implemented due to performance issues.
		if p_student_id is null and v_error_handling = 'Itemized' then
			v_error_handling := 'Ruleset';
		end if;
	
		-- Ensure an immunization was selected to run against.
		if p_immunization is null then
			return null;
		else
			-- Pull the immunization_id.
			select
				i.id into v_imm_id
			from imm_immunizations i
			join imm_vaccines v
				on v.immunization_id = i.id
			where i.code = p_immunization
				and v.ruleset_group_id = v_selected_ruleset_group_id;
			-- Test to make sure this immunization exists in the current selected ruleset group.
			if v_imm_id is null then
				return null;
			end if;
		end if;
	
		-- Singleton student override query value.
		if p_student_id is not null then
			v_added_filter := concat(' and cfle.source_id = ', p_student_id);
		end if;
	
		-- Generate v_sql.
		v_sql := '
		with t1 as (
			select
				student_id,
				vaccine_class,
		';
	
		for cr in
			select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
		loop
			v_sql := concat(v_sql, cr.code, ' ', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order , ', ');
		end loop;
	
		if p_student_id is null then
			v_sql := concat(substring(v_sql for length(v_sql) - 2), ' from fn_imm_calc_data(null, ', p_syear, ')');
		else
			v_sql := concat(substring(v_sql for length(v_sql) - 2), ' from fn_imm_calc_data(', p_student_id, ', ', p_syear, ')');
		end if;
	
		v_sql := concat(v_sql, ' group by 1, 2),
		t2 as (
			select
				student_id,
		');
	
		for cr in
			select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
		loop
			if cr.score is null then
				v_sql := concat(v_sql, 'case when max(', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order , ') >= 1 then 1 else 0 end ', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order, ', ');
			else
				v_sql := concat(v_sql, 'case when max(', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order , ') ', cr.operand, ' ', cr.score, ' then 1 else 0 end ', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order, ', ');
			end if;
		end loop;
	
		v_sql := concat(substring(v_sql for length(v_sql) - 2), ' from t1 group by 1),
		t3 as (
			select
				student_id,
				(select title from imm_immunizations where id = ', v_imm_id, ') vaccine_class,
				cast(cast((');
	
		-- Reset counter.
		v_rs_counter := 1;
		v_rs_selector := 1;
	
		for cr in
			select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
		loop
			if v_rs_selector <> cast(substring(cr.title from length(cr.title)) as int) then
				v_sql := concat(substring(v_sql for length(v_sql) - 3), ') as float)/cast(', v_rs_counter,' as float)*100 as int) rs', v_rs_selector, ', cast(cast((');
				v_sql := concat(v_sql, substring(cr.title from length(cr.title) - 2), '_', cr.rule_order, ' + ');
				v_rs_selector := cast(substring(cr.title from length(cr.title)) as int);
				v_rs_counter := cr.rule_order;
			else
				v_sql := concat(v_sql, ' ', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order, ' + ');
				v_rs_counter := cr.rule_order;
			end if;
		end loop;
	
		v_sql := concat(substring(v_sql for length(v_sql) - 3), ') as float)/cast(', v_rs_counter,' as float)*100 as int) rs', v_rs_selector, ' from t2),');
	
		v_sql := concat(v_sql, '
		t3pe as (
			select
				student_id,
				unnest(array[');
	
		for cr in
			select distinct
				rs.title,
				concat('rs', cast(substring(rs.title from length(rs.title)) as int)) rs,
				rs.t2error,
				gl.code
			from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) rs
			left join fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) gl
				on rs.title = gl.title and gl.grade_limiter = 'Y'
			order by rs.title
		loop
			v_sql := concat(v_sql, '''', cr.rs , ''', ');
		end loop;
	
		v_sql := concat(substring(v_sql for length(v_sql) - 2), ']) rs,
			unnest(array[');
	
		for cr in
			select distinct
				rs.title,
				concat('rs', cast(substring(rs.title from length(rs.title)) as int)) rs,
				rs.t2error,
				gl.code
			from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) rs
			left join fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) gl
				on rs.title = gl.title and gl.grade_limiter = 'Y'
			order by rs.title
		loop
			v_sql := concat(v_sql, cr.rs , ', ');
		end loop;
	
		v_sql := concat(substring(v_sql for length(v_sql) - 2), ']) score,
			unnest(array[');
	
		for cr in
			select distinct
				rs.title,
				concat('rs', cast(substring(rs.title from length(rs.title)) as int)) rs,
				rs.t2error,
				gl.code
			from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) rs
			left join fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) gl
				on rs.title = gl.title and gl.grade_limiter = 'Y'
			order by rs.title
		loop
			v_sql := concat(v_sql, '''', cr.t2error , ''', ');
		end loop;
	
		v_sql := concat(substring(v_sql for length(v_sql) - 2), ']) error_msg,
			unnest(array[');
	
		for cr in
			select distinct
				rs.title,
				concat('rs', cast(substring(rs.title from length(rs.title)) as int)) rs,
				rs.t2error,
				gl.code
			from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) rs
			left join fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) gl
				on rs.title = gl.title and gl.grade_limiter = 'Y'
			order by rs.title
		loop
			if cr.code is not null then
				if p_student_id is null then
					v_sql := concat(v_sql, '(select ', cr.code , ' from fn_imm_calc_data(null, ', p_syear, ') where vaccine_class = (select title from imm_immunizations where id = ', v_imm_id, ')), ');
				else
					v_sql := concat(v_sql, '(select ', cr.code , ' from fn_imm_calc_data(', p_student_id, ', ', p_syear, ') where vaccine_class = (select title from imm_immunizations where id = ', v_imm_id, ')), ');
				end if;
			else
				v_sql := concat(v_sql, ' 0, ');
			end if;
		end loop;
	
		v_sql := concat(substring(v_sql for length(v_sql) - 2),']) gl_test,
			unnest(array[');
	
		for cr in
			select distinct
				rs.title,
				concat('rs', cast(substring(rs.title from length(rs.title)) as int)) rs,
				rs.t2error,
				gl.code
			from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) rs
			left join fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear) gl
				on rs.title = gl.title and gl.grade_limiter = 'Y'
			order by rs.title
		loop
			if cr.code is not null then
				v_sql := concat(v_sql, ' 1, ');
			else
				v_sql := concat(v_sql, ' 0, ');
			end if;
		end loop;
	
		v_sql := concat(substring(v_sql for length(v_sql) - 2),']) g_test from t3),
			t3e as (select *, rank() over (partition by student_id order by gl_test desc, score desc, rs) from t3pe where (g_test = 1 and gl_test > 0) or g_test = 0),
		');
	
		if v_error_handling = 'Itemized' then
	
			v_sql := concat(v_sql,
			' eitems as (
				select
					concat(substring(r.title from length(r.title) - 2), ''_'', rr.rule_order) rs,
					rr.error_msg,
					rgr.error_msg imm_error_msg
				from imm_rulesets r
				join imm_rulesets_rules rr
					on rr.ruleset_id = r.id
				join imm_ruleset_groups_rulesets rgr
					on rgr.ruleset_id = r.id
				where rgr.immunization_id = ', v_imm_id, '
					and (', p_syear, ' between rgr.start_dt and rgr.end_dt or (rgr.end_dt is null and rgr.start_dt <= ', p_syear, '))
					and (', p_syear, ' between rr.start_dt and rr.end_dt or (rr.end_dt is null and rr.start_dt <= ', p_syear, '))
				order by r.title, rr.rule_order
			),
			escores as (
				select
					student_id,
					unnest(array[');
	
			for cr in
				select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
			loop
				v_sql := concat(v_sql, '''', substring(cr.title from length(cr.title) - 2), '_', cr.rule_order , ''', ');
			end loop;
	
			v_sql := concat(substring(v_sql for length(v_sql) - 2),']) rs,
			unnest(array[');
	
			for cr in
				select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
			loop
				v_sql := concat(v_sql, substring(cr.title from length(cr.title) - 2), '_', cr.rule_order , ', ');
			end loop;
	
			v_sql := concat(substring(v_sql for length(v_sql) - 2),']) score
				from t2
			),
			err_item as (
				select
					es.student_id,
					ei.imm_error_msg,
					string_agg(ei.error_msg, '' '' order by es.rs) error_msg
				from escores es
				join eitems ei
					on ei.rs = es.rs
				join t3e
					on t3e.rs = substring(lower(es.rs) for 3) and t3e.rank = 1
				where es.score = 0
				group by es.student_id, ei.imm_error_msg
			),');
	
		end if;
	
		v_sql := concat(v_sql, '
		vars as (
			select ', v_imm_col_id, ' cfid
		)
		, imm as (
			select distinct
				i.id,
				i.code
			from imm_immunizations i
			join imm_ruleset_groups_rulesets rgr
				on rgr.immunization_id = i.id
			join imm_ruleset_groups rg
				on rg.id = rgr.ruleset_group_id
			where rg.title = (select value from program_config where title = ''IMM_SELECTED_RULESET_GROUP'' and syear = (select cast(value as int) from program_config where program = ''system'' and title = ''DEFAULT_S_YEAR'' and syear is null and school_id is null))
				and ', p_syear ,' >= start_dt
				and (', p_syear ,' <= end_dt or end_dt is null)
		)
		, exempt as (
			select distinct
				cfle.source_id as student_id,
				i.title as vaccine_class,
				cfso.label as exemption
			from custom_field_log_entries cfle
			join custom_field_select_options cfso
				on cast(cfso.id as varchar) = cfle.log_field2
			join imm_immunizations i
				on cast(i.id as varchar) = cfle.log_field1
			join imm_ruleset_groups_rulesets rgr
				on rgr.immunization_id = i.id
			join imm_ruleset_groups rg
				on rg.id = rgr.ruleset_group_id
			where rg.title = (select value from program_config where title = ''IMM_SELECTED_RULESET_GROUP'' and syear = (select cast(value as int) from program_config where program = ''system'' and title = ''DEFAULT_S_YEAR'' and syear is null and school_id is null))
				and ', p_syear ,' >= start_dt
				and (', p_syear ,' <= end_dt or end_dt is null)
				and cfle.field_id = (select cast(code as int) from imm_config where title = ''IMM_EXEMPT_FIELD_ID'') ',
				v_added_filter
				, ' and current_date between cast(cfle.log_field3 as date)
				and coalesce(cast(cfle.log_field4 as date), current_date)
		),
		data as (
			select distinct
				t3.student_id,
				(select id from imm where lower(code) = ''', p_immunization, ''') as vaccine_id,
				case when e.vaccine_class = ''', (select title from imm_immunizations where id = v_imm_id), ''' and e.exemption is not null then ''Y''
		');
	
		-- Reset counter.
		v_rs_counter := 0;
	
		for cr in
			select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
		loop
			if v_rs_counter <> cast(substring(cr.title from length(cr.title)) as int) then
				v_rs_counter := cast(substring(cr.title from length(cr.title)) as int);
				v_sql := concat(v_sql, ' when t3.rs', v_rs_counter, ' = 100 then ''Y'' ');
			end if;
		end loop;
	
		v_sql := concat(v_sql, ' else ''N'' end as compliant,
		case when e.vaccine_class = t3.vaccine_class and e.exemption is not null then ''Student is exempt.'' ');
	
		-- Reset counter.
		v_rs_counter := 0;
	
		for cr in
			select * from fn_imm_get_rulesets((select title from imm_ruleset_groups where id = v_selected_ruleset_group_id), p_immunization, p_syear)
		loop
			if v_rs_counter <> cast(substring(cr.title from length(cr.title)) as int) then
				v_rs_counter := cast(substring(cr.title from length(cr.title)) as int);
				if v_error_handling = 'Generic' then
					v_sql := concat(v_sql, ' when t3.rs', v_rs_counter, ' = 100 then ''', cr.t1comp, ''' ');
				else
					v_sql := concat(v_sql, ' when t3.rs', v_rs_counter, ' = 100 then ''', cr.t2comp, ''' ');
				end if;
			end if;
		end loop;
	
		if v_error_handling = 'Generic' then
			v_sql := concat(v_sql, ' else ''', cr.t1error, ''' end as required_doses,');
		elseif v_error_handling = 'Ruleset' then
			v_sql := concat(v_sql, ' else t3e.error_msg end as required_doses,');
		elseif v_error_handling = 'Itemized' then
			v_sql := concat(v_sql, ' else concat(ei.imm_error_msg, '' '', ei.error_msg) end as required_doses,');
		else
			v_sql := concat(v_sql, ' else null end as required_doses,');
		end if;
	
		v_sql := concat(v_sql, ' case when e.exemption is null then ''No exemption'' else e.exemption end as exemption_msg,
		(select cfid from vars) as custom_field_id
		from t3 ');
	
		if v_error_handling <> 'Generic' then
			v_sql := concat(v_sql, ' join t3e
				on t3e.student_id = t3.student_id and t3e.rank = 1');
		end if;
	
		if v_error_handling = 'Itemized' then
			v_sql := concat(v_sql, ' left join err_item ei
				on ei.student_id = t3.student_id');
		end if;
	
		v_sql := concat(v_sql, ' left join exempt e
			on e.student_id = t3.student_id and e.vaccine_class = t3.vaccine_class),
		cte_update as (
			update custom_field_log_entries cfle set
				log_field2 = d.compliant,
				log_field3 = d.required_doses,
				log_field4 = d.exemption_msg,
				log_field5 = ''u''
			from data as d
			join custom_field_log_entries le on d.student_id = le.source_id
				and d.custom_field_id = le.field_id
				and cast(d.vaccine_id as varchar) = le.log_field1
			where cfle.id = le.id
				and (cfle.log_field2 <> d.compliant
					or cfle.log_field3 <> d.required_doses
					or cfle.log_field4 <> d.exemption_msg)
	
			returning d.*
		)
		insert into custom_field_log_entries (
			id,
			 source_class,
			source_id,
			field_id,
			log_field1,
			log_field2,
			log_field3,
			log_field4,
			log_field5
		)
		select
			nextval(''custom_field_log_entries_seq''),
			''SISStudent'',
			d.student_id,
			d.custom_field_id,
			d.vaccine_id,
			d.compliant,
			d.required_doses,
			d.exemption_msg,
			''i''
		from data as d
		left join cte_update as u
			on u.student_id = d.student_id
				and u.custom_field_id = d.custom_field_id
				and u.vaccine_id = d.vaccine_id
		where not exists (select ''''
			from custom_field_log_entries c
			where c.source_id = d.student_id
				and c.field_id = d.custom_field_id
				and c.log_field1 = cast(d.vaccine_id as varchar)
		);');
	
		if p_debug <> 1 then
			-- Run the query.
			execute v_sql;
		else
			-- Return the query.
			return v_sql;
		end if;
	
		-- Exception to remove Florda Hep B2 or Hep B3 No's when the other is Yes.
		if p_immunization in ('hepb2', 'hepb3') then
			for cr in
				with rgr as (
					select distinct
						rgr.immunization_id
					from imm_ruleset_groups_rulesets rgr
					where rgr.ruleset_group_id = v_selected_ruleset_group_id
						and p_syear >= rgr.start_dt
						and (p_syear <= rgr.end_dt or rgr.end_dt is null)
				),
				ret_recs as (
					select
						cfle.source_id,
						max(case when cfle.log_field2 = 'N' then cfle.id else null end) cfle_id,
						sum(case when cfle.log_field2 = 'Y' then 1 else 0 end) as has_yes
					from imm_immunizations i
					join rgr
						on rgr.immunization_id = i.id
					join custom_field_log_entries cfle
						on cfle.log_field1 = cast(i.id as varchar)
					where i.code in ('hepb2', 'hepb3')
						and (cfle.source_id = p_student_id or coalesce(p_student_id, 1) = 1)
					group by cfle.source_id
				)
				select
					cfle_id,
					source_id
				from ret_recs
				where cfle_id is not null
					and has_yes > 0
			loop
				delete from custom_field_log_entries where id = cr.cfle_id and source_id = cr.source_id and source_class = 'SISStudent' and log_field2 = 'N';
			end loop;
		end if;
		return null;
	
	end;
	$$ language plpgsql;
	
	");







