<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

//Data for custom fields
$demo_fields = [
	[
		'title' => 'Status',
		'column' => 'charter_status',
		'type' => 'select'
	],
	[
		'title' => 'Survey 2, 3 and 5',
		'column' => 'svy235_ph',
		'type' => 'holder'
	],
	[
		'title' => 'Primary School',
		'column' => 'charter_prim_schl',
		'type' => 'select',
		'option_query' => 'SELECT f.id as id, concat(f.code, \' - \', f.name) as label FROM gl_facilities f'
	],
	[
		'title' => 'Primary Job Code',
		'column' => 'charter_prim_job',
		'type' => 'select',
		'option_query' => 'SELECT s.id as id, concat(s.code, \' - \', s.title) as label FROM gl_pr_jobs_state s'
	],
	[
		'title' => 'Employee Type',
		'column' => 'charter_emp_type',
		'type' => 'select'
	],
	[
		'title' => 'Current Position Date',
		'column' => 'charter_curr_position',
		'type' => 'date'
	],
	[
		'title' => 'Continuous Employment Date',
		'column' => 'charter_cont_date',
		'type' => 'date'
	],
	[
		'title' => 'Original Position Date',
		'column' => 'charter_orig_date',
		'type' => 'date'
	],
	[
		'title' => 'Midpoint Evaluation for Newly Hired Classroom Teachers',
		'column' => 'charter_mid_eval',
		'type' => 'select',
		'desc' => 'Survey 3 Only',
		'option_query' => 'SELECT e.id as id, concat(e.code, \' - \', e.title) as label FROM gl_pr_personnel_evaluation_codes e'
	],
	[
		'title' => 'Separation Date',
		'column' => 'charter_sep_date',
		'type' => 'date'
	],
	[
		'title' => 'Separation Reason',
		'column' => 'charter_sep_rsn',
		'type' => 'select'
	],
	[
		'title' => 'Survey 2 & 3',
		'column' => 'Svy23p',
		'type' => 'holder'
	],
	[
		'title' => 'Payroll Information - Base',
		'column' => 'charter_payroll_info',
		'type' => 'log'
	],
	[
		'title' => 'Payroll Information - Salary Adjustment',
		'column' => 'charter_prinfo_sal_adj',
		'type' => 'log'
	],
	[
		'title' => 'Additional Job Assignment',
		'column' => 'charter_addtl_job',
		'type' => 'log'
	],
	[
		'title' => 'Additional Compensation',
		'column' => 'charter_addtl_comp',
		'type' => 'log'
	],
	[
		'title' => 'Benefits',
		'column' => 'charter_benefits',
		'type' => 'log'
	],
	[
		'title' => 'Experience',
		'column' => 'charter_exper',
		'type' => 'log'
	],
	[
		'title' => 'Multi-District Employee (Survey 2 Only)',
		'column' => 'charter_multi_dist',
		'type' => 'log'
	],
	[
		'title' => 'Survey 5',
		'column' => 'svy5_ph',
		'type' => 'holder'
	],
	[
		'title' => 'Charter Staff Attendance',
		'column' => 'charter_staff_attend',
		'type' => 'log'
	],
	[
		'title' => 'Final Evaluation Code',
		'column' => 'charter_final_eval',
		'type' => 'select',
		'option_query' => 'SELECT e.id as id, concat(e.code, \' - \', e.title) as label FROM gl_pr_personnel_evaluation_codes e'
	],
	[
		'title' => 'IL',
		'column' => 'charter_eval_il',
		'desc' => 'Instructional Leadership Component',
		'type' => 'numeric'
	],
	[
		'title' => 'IP',
		'column' => 'charter_eval_ip',
		'desc' => 'Instructional Practice Component',
		'type' => 'numeric'
	],
	[
		'title' => 'PJR',
		'column' => 'charter_eval_pjr',
		'desc' => 'Professional and Job Responsibilities Component',
		'type' => 'numeric'
	],
	[
		'title' => 'SP',
		'column' => 'charter_eval_sp',
		'desc' => 'Student Performance Component',
		'type' => 'numeric'
	],
	[
		'title' => 'MSP',
		'column' => 'charter_eval_msp',
		'desc' => 'Measures of Student Performance',
		'type' => 'select',
		'option_query' => 'SELECT m.id as id, concat(m.code, \' - \', m.title) as label FROM gl_pr_measures_of_student_learning_growth m'
	],
	[
		'title' => 'Fiscal Year Salaries',
		'column' => 'charter_fy_salaries',
		'type' => 'log'
	],
	[
		'title' => 'FY Salaries - Addt\'l Compensation',
		'column' => 'charter_fysal_addl_comp',
		'type' => 'log'
	],
	[
		'title' => 'Fiscal Year Benefits',
		'column' => 'charter_fy_benefits',
		'type' => 'log'
	]
];


//Data for custom logging fields
$log_columns = [
	[
		'title' => 'Type',
		'type' => 'select',
		'col_name' => 'charter_addtl_comp',
		's_order' => 1
	],
	[
		'title' => 'Amount',
		'type' => 'numeric',
		'col_name' => 'charter_addtl_comp',
		's_order' => 2
	],
	[
		'title' => 'School',
		'type' => 'select',
		'col_name' => 'charter_addtl_job',
		's_order' => 1,
		'option_query' => 'SELECT f.id as id, concat(f.code, \' - \', f.name) as label FROM gl_facilities f'

	],
	[
		'title' => 'Job Code',
		'type' => 'select',
		'col_name' => 'charter_addtl_job',
		's_order' => 2,
		'option_query' => 'SELECT s.id as id, concat(s.code, \' - \', s.title) as label FROM gl_pr_jobs_state s'
	],
	[
		'title' => 'FTE',
		'type' => 'numeric',
		'col_name' => 'charter_addtl_job',
		's_order' => 3
	],
	[
		'title' => 'Benefit Type',
		'type' => 'select',
		'col_name' => 'charter_benefits',
		's_order' => 1
	],
	[
		'title' => 'Amount',
		'type' => 'numeric',
		'col_name' => 'charter_benefits',
		's_order' => 2
	],
	[
		'title' => 'Frequency',
		'type' => 'numeric',
		'col_name' => 'charter_benefits',
		's_order' => 3
	],
	[
		'title' => 'Days Present',
		'type' => 'numeric',
		'col_name' => 'charter_staff_attend',
		's_order' => 1
	],
	[
		'title' => 'Days Absent - Personal Leave',
		'type' => 'numeric',
		'col_name' => 'charter_staff_attend',
		's_order' => 2
	],
	[
		'title' => 'Days Absent - Sick Leave',
		'type' => 'numeric',
		'col_name' => 'charter_staff_attend',
		's_order' => 3
	],
	[
		'title' => 'Days Absent - Temp Duty Elsewhere',
		'type' => 'numeric',
		'col_name' => 'charter_staff_attend',
		's_order' => 4
	],
	[
		'title' => 'Days Absent - Other',
		'type' => 'numeric',
		'col_name' => 'charter_staff_attend',
		's_order' => 5
	],
	[
		'title' => 'Experience Type',
		'type' => 'select',
		'col_name' => 'charter_exper',
		's_order' => 1
	],
	[
		'title' => 'Years',
		'type' => 'numeric',
		'col_name' => 'charter_exper',
		's_order' => 2
	],
	[
		'title' => 'School',
		'type' => 'select',
		'col_name' => 'charter_fy_benefits',
		's_order' => 1,
		'option_query' => 'SELECT f.id as id, concat(f.code, \' - \', f.name) as label FROM gl_facilities f'

	],
	[
		'title' => 'Job Code',
		'type' => 'select',
		'col_name' => 'charter_fy_benefits',
		's_order' => 2,
		'option_query' => 'SELECT s.id as id, concat(s.code, \' - \', s.title) as label FROM gl_pr_jobs_state s'
	],
	[
		'title' => 'Benefits Type',
		'type' => 'select',
		'col_name' => 'charter_fy_benefits',
		's_order' => 3
	],
	[
		'title' => 'Amount',
		'type' => 'numeric',
		'col_name' => 'charter_fy_benefits',
		's_order' => 4
	],
	[
		'title' => 'School',
		'type' => 'select',
		'col_name' => 'charter_fy_salaries',
		's_order' => 1,
		'option_query' => 'SELECT f.id as id, concat(f.code, \' - \', f.name) as label FROM gl_facilities f'

	],
	[
		'title' => 'Job Code',
		'type' => 'select',
		'col_name' => 'charter_fy_salaries',
		's_order' => 2,
		'option_query' => 'SELECT s.id as id, concat(s.code, \' - \', s.title) as label FROM gl_pr_jobs_state s'
	],
	[
		'title' => 'FY Salaries',
		'type' => 'numeric',
		'col_name' => 'charter_fy_salaries',
		's_order' => 3
	],
	[
		'title' => 'Status',
		'type' => 'select',
		'col_name' => 'charter_fy_salaries',
		's_order' => 4
	],
	[
		'title' => 'TI-SW',
		'type' => 'select',
		'col_name' => 'charter_fy_salaries',
		'desc' => 'Title I - School Wide',
		's_order' => 5
	],
	[
		'title' => 'TI-SW FTE',
		'type' => 'numeric',
		'col_name' => 'charter_fy_salaries',
		'desc' => 'Title I - School Wide FTE',
		's_order' => 6
	],
	[
		'title' => 'TI-TA',
		'type' => 'select',
		'col_name' => 'charter_fy_salaries',
		'desc' => 'Title I - Targeted Assistance',
		's_order' => 7
	],
	[
		'title' => 'TI-TA FTE',
		'type' => 'numeric',
		'col_name' => 'charter_fy_salaries',
		'desc' => 'Title I - Targeted Assistance FTE',
		's_order' => 8
	],
	[
		'title' => 'MS',
		'type' => 'select',
		'col_name' => 'charter_fy_salaries',
		'desc' => 'Migrant Summer',
		's_order' => 9
	],
	[
		'title' => 'MS FTE',
		'type' => 'numeric',
		'col_name' => 'charter_fy_salaries',
		'desc' => 'Migrant Summer FTE',
		's_order' => 10
	],
	[
		'title' => 'MR',
		'type' => 'select',
		'col_name' => 'charter_fy_salaries',
		'desc' => 'Migrant Regular School Year',
		's_order' => 11
	],
	[
		'title' => 'MR FTE',
		'type' => 'numeric',
		'col_name' => 'charter_fy_salaries',
		'desc' => 'Migrant Regular School Year FTE',
		's_order' => 12
	],
	[
		'title' => 'School',
		'type' => 'select',
		'col_name' => 'charter_fysal_addl_comp',
		's_order' => 1,
		'option_query' => 'SELECT f.id as id, concat(f.code, \' - \', f.name) as label FROM gl_facilities f'

	],
	[
		'title' => 'Job Code',
		'type' => 'select',
		'col_name' => 'charter_fysal_addl_comp',
		's_order' => 2,
		'option_query' => 'SELECT s.id as id, concat(s.code, \' - \', s.title) as label FROM gl_pr_jobs_state s'
	],
	[
		'title' => 'Addt\'l Comp Type',
		'type' => 'select',
		'col_name' => 'charter_fysal_addl_comp',
		's_order' => 3
	],
	[
		'title' => 'Amount',
		'type' => 'numeric',
		'col_name' => 'charter_fysal_addl_comp',
		's_order' => 4
	],
	[
		'title' => 'Assignment Identifier',
		'type' => 'select',
		'col_name' => 'charter_multi_dist',
		's_order' => 1
	],
	[
		'title' => 'District',
		'type' => 'numeric',
		'col_name' => 'charter_multi_dist',
		's_order' => 2
	],
	[
		'title' => 'Status',
		'type' => 'select',
		'col_name' => 'charter_payroll_info',
		's_order' => 1
	],
	[
		'title' => 'School',
		'type' => 'select',
		'col_name' => 'charter_payroll_info',
		's_order' => 2,
		'option_query' => 'SELECT f.id as id, concat(f.code, \' - \', f.name) as label FROM gl_facilities f'

	],
	[
		'title' => 'Job Code',
		'type' => 'select',
		'col_name' => 'charter_payroll_info',
		's_order' => 3,
		'option_query' => 'SELECT s.id as id, concat(s.code, \' - \', s.title) as label FROM gl_pr_jobs_state s'
	],
	[
		'title' => 'Emp Type',
		'type' => 'select',
		'col_name' => 'charter_payroll_info',
		's_order' => 4
	],
	[
		'title' => 'FTE',
		'type' => 'numeric',
		'col_name' => 'charter_payroll_info',
		's_order' => 5
	],
	[
		'title' => 'Days',
		'type' => 'numeric',
		'col_name' => 'charter_payroll_info',
		's_order' => 6
	],
	[
		'title' => 'Months',
		'type' => 'numeric',
		'col_name' => 'charter_payroll_info',
		's_order' => 7
	],
	[
		'title' => 'Salary',
		'type' => 'numeric',
		'col_name' => 'charter_payroll_info',
		's_order' => 8
	],
	[
		'title' => 'Pay Type',
		'type' => 'select',
		'col_name' => 'charter_payroll_info',
		's_order' => 9
	],
	[
		'title' => 'Step',
		'type' => 'numeric',
		'col_name' => 'charter_payroll_info',
		's_order' => 10
	],
	[
		'title' => 'Contract',
		'type' => 'select',
		'col_name' => 'charter_payroll_info',
		's_order' => 11
	],
	[
		'title' => 'Grandfathered Indicator',
		'type' => 'select',
		'col_name' => 'charter_payroll_info',
		's_order' => 12
	],
	[
		'title' => 'School',
		'type' => 'select',
		'col_name' => 'charter_prinfo_sal_adj',
		's_order' => 1,
		'option_query' => 'SELECT f.id as id, concat(f.code, \' - \', f.name) as label FROM gl_facilities f'

	],
	[
		'title' => 'Job Code',
		'type' => 'select',
		'col_name' => 'charter_prinfo_sal_adj',
		's_order' => 2,
		'option_query' => 'SELECT s.id as id, concat(s.code, \' - \', s.title) as label FROM gl_pr_jobs_state s'
	],
	[
		'title' => 'Adjustment Type',
		'type' => 'select',
		'col_name' => 'charter_prinfo_sal_adj',
		's_order' => 3,
	],
	[
		'title' => 'Adjustment Amount',
		'type' => 'numeric',
		'col_name' => 'charter_prinfo_sal_adj',
		's_order' => 4,
	]
];


//Select Option for custom fields
$select_options = [
	'charter_sep_rsn' => [
		'A' => 'A - Retirement',
		'B' => 'B - Resignation for employment in education in Florida',
		'C' => 'C - Resignation for employment outside of education',
		'D' => 'D - Resignation with prejudice',
		'E' => 'E - Resignation for other personal reasons',
		'F' => 'F - Staff reduction',
		'G' => 'G - Dismissal due to findings by the board related to charges',
		'H' => 'H - Death',
		'I' => 'I - Contract expired',
		'J' => 'J - Reason not known',
		'K' => 'K - Disabled',
		'L' => 'L - Resignation for employment in education outside Florida',
		'M' => 'M - Contract not renewed, due to less than satisfactory performance',
		'N' => 'N - Dismissal during probationary period',
		'O' => 'O - Job Abandonment',
		'P' => 'P - Classroom teachers or principals who were dismissed for ineffective performance as demonstrated through the district&rsquo;s evaluation system',
		'Z' => 'Z - Not applicable. Include temporary employees here'
	],
	'charter_status' => [
		'A' => 'Active',
		'I' => 'Inactive'
	],
	'charter_emp_type' => [
		'RF' => 'RF - Regular full-time employee',
		'CF' => 'CF - Contracted full-time employee',
		'CP' => 'CP - Contracted part-time employee',
		'RP' => 'RP - Regular part-time employee',
		'ST' => 'ST - Student employee',
		'TF' => 'TF - Temporary full-time employee',
		'TP' => 'TP - Temporary part-time employee'
	]
];

//Select Option for custom logging fields
$log_select_options = [
	'charter_addtl_comp_1' => [     //Type
		'1' => '1 - Supplement for assignment to a school in the bottom two categories of the school improvement system under s.1012.22(1)(c )(5)(c )(II), F.S. such that the supplement remains in force for at least 1 year following improved performance in that school.',
		'2' => '2 - Supplement for certification and teaching in critical teacher shortage areas per s. 1012.22(1)(c)(5)(c)(III)F.S. Statewide critical teacher shortage areas are identified by the State Board of Education under s.1012.07, F.S. However, the district school board may identify other areas of critical shortage within the school district for district purposes and may remove areas identified by the State Board which do not apply within the school district.',
		'3' => '3 - Supplement for assignment of additional academic responsibilities per s. 1012.22(1)(c )(5)(c )(IV)F.S.',
		'4' => '4 - Bonus for instruction in a course that led to a CAPE Industry Certification per s. 1011.62(1)(o), F.S.',
		'A' => 'A - Supplement for Athletic - includes additional compensation to athletic directors, trainers, head coaches, assistant coaches, etc.',
		'B' => 'B - Supplement for Academic - includes additional compensation to band directors, department heads, cheerleader sponsors, yearbook directors, drama sponsors, etc.',
		'E' => 'E - Supplement for Inservice Stipends - additional compensation paid to an employee who has completed certain inservice hours, coursework, or other training.',
		'F' => 'F - Supplement for Extended Day - additional compensation to those who teach an extended period for extra pay beyond the regular contracted day, including teaching during a planning period and after-school tutoring.',
		'G' => 'G - Other',
		'H' => 'H - Bonus for Florida Excellent Teaching Program - includes salary bonuses and mentoring bonuses as indicated in s. 1012.72, F.S.',
		'I' => 'I - Florida School Recognition Program as defined in s. 1008.36, F.S.',
		'J' => 'J - Bonus for Performance',
		'K' => 'K - Bonus for Advanced Placement Instruction as defined in s. 1011.62(1)(n), F.S.',
		'N' => 'N - Bonus for Teacher Retention in an area of critical state concern.',
		'O' => 'O - Bonus for Teacher Recruitment in an area of critical state concern.',
		'P' => 'P - Bonus for teacher (instructional personnel) retention.',
		'Q' => 'Q - Bonus for International Baccalaureate instruction as defined in s. 1011.62(1)(l), F.S.',
		'R' => 'R - Bonus for teacher (instructional personnel) recruitment.',
		'S' => 'S - S Sick Leave Buy Back &ndash; payment for unused sick leave.',
		'T' => 'T - Terminal Pay &ndash; payment for unused annual leave.',
		'U' => 'U - In-Kind Compensation &ndash; examples: uniforms, car, etc.',
		'V' => 'V - Sabbatical Leave Pay.',
		'W' => 'W - Bonus for Advance International Certificate of Education as defined in s. 1011.62(1)(m), F.S.',
		'Y' => 'Y - Supplement for Advanced Degree (in area of certification) as defined in s. 1012.22(1)(c)3,F.S.',
		'Z' => 'Z - Supplement for assignment to a Title 1 eligible school per s. 1012.22(1)(c )(5)(c )(I)F.S.'
	],
	'charter_benefits_1' => [     //Benefit Type
		'A' => 'A - Health & Hospitalization',
		'B' => 'B - Life Insurance',
		'C' => 'C - Social Security',
		'D' => 'D - Florida Retirement System',
		'E' => 'E - Commercial or Mutual Insurance Annuity Plan',
		'F' => 'F - Unemployment Compensation',
		'G' => 'G - Worker\'s Compensation',
		'K' => 'K - Cafeteria Plan',
		'L' => 'L - Other',
		'M' => 'M - Medicare',
		'N' => 'N - Cafeteria Plan - Administrative Costs'
	],
	'charter_exper_1' => [     //Experience Type
		'A' => 'A - Administration in education',
		'C' => 'C - Service to the district in current job code assignment',
		'D' => 'D - Teaching in current district',
		'F' => 'F - Teaching in Florida public schools',
		'M' => 'M - Military service',
		'N' => 'N - Teaching in out-of-state nonpublic schools',
		'P' => 'P - Teaching in out-of-state public schools',
		'S' => 'S - Teaching in Florida non-public schools'
	],
	'charter_fy_benefits_3' => [     //Benefits Type
		'A' => 'A - Health and Hospitalization',
		'B' => 'B - Life Insurance',
		'C' => 'C - Social Security',
		'D' => 'D - Florida Retirement System',
		'E' => 'E - Commercial or Mutual Insurance Annuity Plan',
		'F' => 'F - Unemployment Compensation',
		'G' => 'G - Worker&rsquo;s Compensation',
		'K' => 'K - Cafeteria Plan',
		'L' => 'L - Other',
		'M' => 'M - Medicare',
		'N' => 'N - Cafeteria Plan - Administrative Costs',
		'Z' => 'Z - No Benefits'
	],
	'charter_fysal_addl_comp_3' => [     //Addt'l Comp Type
		'0' => '0 - No additional compensation',
		'1' => '1 - Supplement for assignment to a school in the bottom two categories of the school improvement system 	under s. 1012.22(1)(c )(5)(c )(II)F.S. such that the supplement remains in force for at least 1 year following improved performance in that school.',
		'2' => '2 - Supplement for certification and teaching in critical teacher shortage areas per s. 1012.22(1)(c)(5)(c)(III)F.S. Statewide critical teacher shortage areas are identified by the State Board of Education under s.1012.07, F.S. However, the district school board may identify other areas of critical shortage within the school district for district purposes and may remove areas identified by the State Board which do not apply within the school district.',
		'3' => '3 - Supplement for assignment of additional academic responsibilities per s. 1012.22(1)(c)(5)(c)(IV)F.S',
		'4' => '4 - Bonus for instruction in a course that led to a CAPE Industry Certification per s. 1011.62(1)(o), F.S.',
		'5' => '5 - Florida Best and Brightest Teacher Scholarship Program as defined in Chapter 2015-232 (S.A. 99A).',
		'A' => 'A - Supplement for Athletic - includes additional compensation to athletic directors, trainers, head coaches, assistant coaches, etc.',
		'B' => 'B - Supplement for Academic - includes additional compensation to band directors, department heads, cheerleader sponsors, yearbook directors, drama sponsors, etc.',
		'E' => 'E - Supplement for Inservice Stipends -additional compensation paid to an employee who has completed certain inservice hours, coursework, or other training.',
		'F' => 'F - Supplement for Extended Day - additional compensation to those who teach an extended period for extra pay beyond the regular contracted day, including teaching during a planning period or after-school tutoring',
		'G' => 'G - Other',
		'H' => 'H - Bonus for Florida Excellent Teaching Program - includes salary bonuses and mentoring bonuses as indicated in s. 1012.72, F.S.',
		'I' => 'I - Florida School Recognition Program as defined in s. 1008.36, F.S.',
		'J' => 'J - Bonus for Performance',
		'K' => 'K - Bonus for Advanced Placement Instruction as defined in s. 1011.62(1)(n), F.S.',
		'N' => 'N - Bonus for Teacher Retention in an area of critical state concern.',
		'O' => 'O - Bonus for Teacher Recruitment in an area of critical state concern.',
		'P' => 'P - Bonus for teacher (instructional personnel) retention.',
		'Q' => 'Q - Bonus for International Baccalaureate instruction as defined in s. 1011.62(1)(l), F.S.',
		'R' => 'R - Bonus for teacher (instructional personnel) recruitment.',
		'S' => 'S - Sick Leave Buy Back &ndash; payment for unused sick leave',
		'T' => 'T - Terminal Pay &ndash; Payment for unused annual leave.',
		'U' => 'U - In-Kind Compensation &ndash; Examples: uniforms, car, etc.',
		'V' => 'V - Sabbatical Leave Pay',
		'W' => 'W - Bonus for Advance International Certificate of Education as defined in s. 1011.62(1)(m), F.S',
		'Y' => 'Y - Supplement for Advanced Degree (in area of certification) as defined in s. 1012.22(1)(c)3 F.S.',
		'Z' => 'Z - Supplement for assignment to a Title 1 eligible school per s. 1012.22(1)(c)(5)(c)(I)F.S.'
	],
	'charter_fy_salaries_4' => [     //Status
		'A' => 'A - Active employee',
		'L' => 'L - Leave of absence without pay',
		'P' => 'P - Leave of absence with pay',
		'T' => 'T - Terminated employee - separated from employment with the district'
	],
	'charter_fy_salaries_5' => [     //TI-SW
		'A' => 'A - Administrator (non-clerical)',
		'B' => 'B - Teacher',
		'C' => 'C - Paraprofessional (instructional)',
		'D' => 'D - Paraprofessional (non-instructional)',
		'E' => 'E - Support staff (clerical and non-clerical)',
		'F' => 'F - Other Instructional Staff (counselors, librarians, psychologists, etc.)',
		'Z' => 'Z - This employee was not employed in a Title I, Part A School-wide program and/or was not paid from Title I, Part A funds.'
	],
	'charter_fy_salaries_7' => [     //TI-TA
		'A' => 'A - Administrator (non-clerical)',
		'B' => 'B - Teacher',
		'C' => 'C - Paraprofessional (instructional)',
		'D' => 'D - Paraprofessional (non-instructional)',
		'E' => 'E - Support staff (clerical and non-clerical)',
		'F' => 'F - Other Instructional Staff (counselors, librarians, psychologists, etc.)',
		'Z' => 'Z - This employee was not employed in a Title I, Part A Targeted Assistance program and/or was not paid from Title I, Part A funds.'
	],
	'charter_fy_salaries_9' => [     //MS
		'A' => 'A - Administrators and coordinators (non-clerical)',
		'B' => 'B - Teachers',
		'C' => 'C - Paraprofessionals (instructional)',
		'D' => 'D - Paraprofessionals (non-instructional)',
		'E' => 'E - Counselors',
		'F' => 'F - Recruiters',
		'G' => 'G - Records transfer staff',
		'H' => 'H - Other employee paid from Title I, Part C, MEP funds but not included in codes A-G above.',
		'Z' => 'Z - Employee was not paid from Title I, Part C, Migrant Education Program (MEP) funds during the summer/intersession term(s) or was employed in a school-wide program where MEP funds were combined with those of other programs.'
	],
	'charter_fy_salaries_11' => [     //MR
		'A' => 'A - Administrators and coordinators (non-clerical)',
		'B' => 'B - Teachers',
		'C' => 'C - Paraprofessionals (instructional)',
		'D' => 'D - Paraprofessionals (non-instructional)',
		'E' => 'E - Counselors',
		'F' => 'F - Recruiters',
		'G' => 'G - Records Transfer Staff',
		'H' => 'H - Other employee paid from Title I, Part C, MEP funds but not included in codes A-G above.',
		'Z' => 'Z - Employee was not paid from Title I, Part C, Migrant Education Program funds during the regular school year or was employed in a school-wide program where MEP funds were combined with those of other programs.'
	],
	'charter_multi_dist_1' => [     //Assignment Identifier
		'X' => 'X - Multidistrict consortium employee, in accordance with Rule 6A-1.099, FAC, Cooperative projects and activities.',
		'Y' => 'Y - Employed in more than one district through another formal agreement or employed in projects serving more than one district.'
	],
	'charter_payroll_info_1' => [
		'A' => 'A - Active employee',
		'L' => 'L - Leave of absence without pay',
		'P' => 'P - Leave of absence with pay',
		'T' => 'T - Terminated employee - separated from employment with the district'
	],
	'charter_payroll_info_4' => [
		'RF' => 'RF - Regular full-time employee',
		'CF' => 'CF - Contracted full-time employee',
		'CP' => 'CP - Contracted part-time employee',
		'RP' => 'RP - Regular part-time employee',
		'ST' => 'ST - Student employee',
		'TF' => 'TF - Temporary full-time employee',
		'TP' => 'TP - Temporary part-time employee'
	],
	'charter_payroll_info_9' => [     //Pay Type
		'0' => '0 - Not an instructional employee and/or is not paid on the regular instructional personnel salary schedule. '
	],
	'charter_payroll_info_11' => [     //Contract Status
		'ZZ' => 'ZZ - Employee is a noninstructional staff member, a substitute teacher and/or is not paid on the regular instructional personnel salary schedule'
	],
	'charter_payroll_info_12' => [     //Grandfathered Indicator
		'Z' => 'Z - Not Applicable'
	],
	'charter_prinfo_sal_adj_3' => [     //Adjustment Type
		'A' => 'A - Instructional or school administrative employee rated as &ldquo;highly effective&rdquo; on  the prior year personnel evaluation [s.1012.22 (1)(c)5a(II)b(I), F.S.]',
		'B' => 'B - Instructional or school administrative employee rated as &ldquo;effective&rdquo; on the  prior year personnel evaluation [s.1012.22 (1)(c)5a(II)b(II), F.S.]',
		'C' => 'C - Cost-of-living adjustment [s.1012.22 (1)(c)2, F.S.]',
		'D' => 'D - Salary adjustment for salary schedule step',
		'E' => 'E - Advanced degree value that is part of the base salary for employees hired prior  to July 1, 2011',
		'F' => 'F - Other salary adjustment',
		'Z' => 'Z - No Salary Adjustment'
	]
];


//Add Categories
$erp_cat = new CustomFieldCategory();
$erp_cat->setTitle('Charter/Contracted Emp Info');
$erp_cat->setErp(1);
$erp_cat->setSourceClass('FocusUser');
$erp_cat->setSortOrder(8675309);
$erp_cat->persist();
$erp_cat->fixSortOrders();

//Get Category info
$erp_cat_id = $erp_cat->getId();

$access_profiles = Database::get("SELECT PROFILE_ID FROM PERMISSION WHERE \"key\" = 'hr::demographic'");

$sort_increment = 0;
foreach ($demo_fields as $field) {
	if (empty(FocusUser::getFieldByAlias($field['column']))) {
		//Add field
		$cf = new CustomField();
		$cf->setTitle($field['title']);
		$cf->setAlias($field['column']);
		$cf->setType($field['type']);
		$cf->setSourceClass('FocusUser');
		$cf->setColumnName($field['column']);
		if (!empty($field['desc'])) {
			$cf->setDescription($field['desc']);
		}
		if (!empty($field['option_query'])) {
			$cf->setOptionQuery($field['option_query']);
		}
		if (!empty($field['new'])) {
			$cf->setNewRecord(1);
		}
		$cf->setSystem(1);
		$cf->persist();

		//Get Field info
		$field_id = $cf->getId();
		$field_column = $cf->getColumnName();

		foreach ($access_profiles as $access_profile) {
			Database::query("INSERT INTO PERMISSION (PROFILE_ID, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'FocusUser:{$field_id}:can_view')");
			Database::query("INSERT INTO PERMISSION (PROFILE_ID, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'FocusUser:{$field_id}:can_edit')");
			if ($field['type'] == 'log') {
				Database::query("INSERT INTO PERMISSION (PROFILE_ID, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'FocusUser:{$field_id}:can_create')");
				Database::query("INSERT INTO PERMISSION (PROFILE_ID, \"key\") VALUES ({$access_profile['PROFILE_ID']}, 'FocusUser:{$field_id}:can_delete')");
			}
		}

		//Join to category
		$cfjc = new CustomFieldJoinCategory();
		$cfjc->setCategoryId($erp_cat_id);
		$cfjc->setFieldId($field_id);
		$cfjc->setSortOrder(8675309+$sort_increment);
		$cfjc->persist();

		$sort_increment++;

		$lookup_key = $field['column'];

		//Add Select Options for Custom Fields
		if ($field['type'] == 'select') {
			foreach ($select_options[$lookup_key] as $option_code => $option_label) {
				//Add select option
				$cfso = new CustomFieldSelectOption();
				$cfso->setCode($option_code);
				$cfso->setLabel($option_label);
				$cfso->setSourceId($field_id);
				$cfso->setSourceClass('CustomField');
				$cfso->persist();
			}
		}

		//Add Logging Fields
		if ($field['type'] == 'log') {
			foreach ($log_columns as $log_column) {
				if ($log_column['col_name'] == $field['column']) {
					$lc = new CustomFieldLogColumn();
					$lc->setFieldId($field_id);
					$lc->setTitle($log_column['title']);
					$lc->setType($log_column['type']);
					$lc->setSortOrder($log_column['s_order']);
					$lc->setRequired(1);
					if (!empty($log_column['desc'])) {
						$lc->setDescription($log_column['desc']);
					}
					if (!empty($log_column['option_query'])) {
						$lc->setOptionQuery($log_column['option_query']);
					}
					$lc->persist();

					$log_field_id = $lc->getId();

					//add logging field permissions here

					$lookup_key = $log_column['col_name'] . '_' . $log_column['s_order'];


					//Add Select Options for Logging Fields
					if ($log_column['type'] == 'select') {
						foreach ($log_select_options[$lookup_key] as $log_option_code => $log_option_label) {
							//Add select option for logging field
							$lcso = new CustomFieldSelectOption();
							$lcso->setCode($log_option_code);
							$lcso->setLabel($log_option_label);
							$lcso->setSourceId($log_field_id);
							$lcso->setSourceClass('CustomFieldLogColumn');
							$lcso->persist();
						}
					}
				}
			}
		}
	}
}

$cfjc = new CustomFieldJoinCategory();
$cfjc->fixSortOrders();


Database::commit();
