<?php
// Block run of MS SQL Server code for now.
if (Database::$type === 'mssql') {
	return false;
}

// Make sure FOCUS-14161 has run so that changes can be made.
Migrations::depend('FOCUS-14161');

// Block run if immunizations isn't installed.
if (!Database::tableExists('imm_config')) {
	return false;
}

Database::query("update imm_rules set code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)' where code = 'sum(case when vaccine_class = '':dv1:'' and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv2:'' as date) then 1 else 0 end)';");

Database::query("update imm_rules set code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date + days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)' where code = 'sum(case when vaccine_class = '':dv1:'' and class_dose_num >= :dv2: and cast(shot_date - days_interval as date) >= cast(dob + interval '':dv3:'' as date) then 1 else 0 end)';");
