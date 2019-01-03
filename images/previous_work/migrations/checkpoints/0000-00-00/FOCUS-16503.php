<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

//svn merge svn://focus-sis.com/focus/branches/8.0/dev/FOCUS-16503-6 -r276695:HEAD

Database::begin();

/*
Create class for new tables
Create new tables
Load default values
Create new fields in gl_pr_slots
Use old fields to load new values into the gl_pr_slot table
Add the tables to the florida maint

replace old fields with new fields on slot maint screen.
look to see if the old fields are used anywhere besides survey
Let Austin know - he is going to have to change the roll for the slots and make a roll for the new tables.
Let Sabrina know about the new fields
change the fields in surveys

employee custom fields set to lookup new tables
and load the new fields with old values

!!!!Addition comp is the same as supplement



 */

require_once("{$GLOBALS['staticpath']}/Finance/classes/hr/FloridaSalarySchedule.php");
require_once("{$GLOBALS['staticpath']}/Finance/classes/hr/FloridaFundingCodes.php");


if (!Database::tableExists("gl_hr_florida_salary_schedule")) {
	Database::query("
		CREATE TABLE gl_hr_florida_salary_schedule (
			id bigint,
			deleted int,
		    code varchar(2),
		    title varchar(500),
		    CONSTRAINT salary_schedule_ix PRIMARY KEY(id)
		)
	");

	Database::createColumn('gl_hr_florida_salary_schedule', 'date_beg', 'date');
	Database::createColumn('gl_hr_florida_salary_schedule', 'date_end', 'date');
}

/*
Add fields to the slot table
 */
if (!Database::columnExists("gl_pr_slots", "salary_schedule_id")) {
	Database::createColumn("gl_pr_slots", "salary_schedule_id", "BIGINT");
}

if (!Database::columnExists("gl_pr_slots", "advanced_degree_id")) {
	Database::createColumn("gl_pr_slots", "advanced_degree_id", "BIGINT");
}


/*
Add table gl_pr_supplements
 */

if(!Database::columnExists('gl_pr_supplements', 'date_beg')) {
	Database::createColumn('gl_pr_supplements', 'date_beg', 'date');

	Database::query("
		update gl_pr_supplements
		set date_beg = '2000-01-01'
		");
}

if(!Database::columnExists('gl_pr_supplements', 'date_end')) {
	Database::createColumn('gl_pr_supplements', 'date_end', 'date');
}

if(!Database::columnExists('gl_pr_supplements', 'deleted')) {
	Database::createColumn('gl_pr_supplements', 'deleted', 'int');
}

if(!Database::columnExists('gl_pr_supplements', 'state_reporting_code')) {
	Database::createColumn('gl_pr_supplements', 'state_reporting_code', 'char(1)');
}

if(Database::columnExists('gl_pr_supplements', 'bonus')) {
	Database::dropColumn('gl_pr_supplements', 'bonus');
}


/*
Add table gl_hr_florida_funding_codes
 */

if (!Database::tableExists("gl_hr_florida_funding_codes")) {
	Database::query("
		CREATE TABLE gl_hr_florida_funding_codes (
			id bigint,
			deleted int,
		    code varchar(2),
		    title varchar(500),
		    CONSTRAINT florida_funding_codes_ix PRIMARY KEY(id)
		)
	");

	Database::createColumn('gl_hr_florida_funding_codes', 'date_beg', 'date');
	Database::createColumn('gl_hr_florida_funding_codes', 'date_end', 'date');
}

/*
Add fields to the project table
 */
if (!Database::columnExists("gl_projects", "fl_fund_source_1_id")) {
	Database::createColumn("gl_projects", "fl_fund_source_1_id", "BIGINT");
}

/*
Change the values in project
 */
Database::query("
	update gl_projects
	set fl_fund_source_1_id = (
		select id
		from gl_hr_florida_funding_codes sch
		where sch.code = gl_projects.fl_fund_source_1
	)
	where fl_fund_source_1 is not null
");

/*
Add fields to the position table
 */
if (!Database::columnExists("gl_pr_positions", "fl_fund_source_1_id")) {
	Database::createColumn("gl_pr_positions", "fl_fund_source_1_id", "BIGINT");
}

if (!Database::columnExists("gl_pr_positions", "fl_fund_source_2_id")) {
	Database::createColumn("gl_pr_positions", "fl_fund_source_2_id", "BIGINT");
}

if (!Database::columnExists("gl_pr_positions", "fl_fund_source_3_id")) {
	Database::createColumn("gl_pr_positions", "fl_fund_source_3_id", "BIGINT");
}

/*
Change the values in slots
 */
Database::query("
	update gl_pr_positions
	set fl_fund_source_1_id = (
		select id
		from gl_hr_florida_funding_codes sch
		where sch.code = gl_pr_positions.fl_fund_source_1
	)
	where fl_fund_source_1 is not null
");

Database::query("
	update gl_pr_positions
	set fl_fund_source_2_id = (
		select id
		from gl_hr_florida_funding_codes sch
		where sch.code = gl_pr_positions.fl_fund_source_2
	)
	where fl_fund_source_2 is not null
");

Database::query("
	update gl_pr_positions
	set fl_fund_source_3_id = (
		select id
		from gl_hr_florida_funding_codes sch
		where sch.code = gl_pr_positions.fl_fund_source_3
	)
	where fl_fund_source_3 is not null
");




if(!Database::indexExists("gl_pr_slots", "slots_fyear_idx"))
	Database::query("CREATE INDEX slots_fyear_idx ON gl_pr_slots (fyear)");

	$tmp = Database::get("select * from gl_hr_florida_salary_schedule");
	if (count($tmp) == 0)
	{

		$insert_values = array(
			"0" => "0 Not an instructional employee and/or is not paid on the regular instructional personnel salary schedule.",
			"1" => "1 Bachelor’s",
			"2" => "2 Bachelor’s Plus",
			"3" => "3 Master’s",
			"4" => "4 Master’s Plus",
			"5" => "5 Beyond Master’s Plus",
			"6" => "6 Specialist",
			"7" => "7 Doctorate",
			"8" => "8 Flat Rate - Example: JROTC instructors",
			"A" => "A Instructional personnel or school administrators hired prior to July 1, 2014 paid on a salary schedule that excludes adjustments for advanced degrees.",
			"B" => "B Instructional personnel or school administrators (regardless of the employee’s hire date) paid on a Performance Salary Schedule."
		);

		/*
		Load default values
		 */

		foreach($insert_values as $code=>$title)
		{
			$new_sal = new FloridaSalarySchedule;
			$new_sal
				->setCode($code)
				->setTitle($title)
				->persist();
		}
	}

/*
Add table gl_hr_florida_funding_codes
 */
	$tmp = Database::get("select * from gl_hr_florida_funding_codes");
	if (count($tmp) == 0)
	{
		$insert_values = array(
			"B" => "Elementary and Secondary Education Act (ESEA)",
			"C" => "Charter School, Not Paid Through District",
			"E" => "IDEA - Individuals with Disabilities Education Act",
			"G" => "State/Local Funded Programs (e.g., FEFP, State Categorical Programs)",
			"H" => "Supplemental Academic Instruction (SAI) (FEFP)",
			"M" => "Elementary and Secondary Education Act (ESEA), as amended by NCLB Title I, Part C (Migrant Education Program)",
			"N" => "State Fiscal Stabilization Funds (ARRA)",
			"O" => "Other Federal Programs",
			"P" => "Targeted ARRA Stimulus Funds",
			"Q" => "Other ARRA Stimulus Grants",
			"R" => "Reading First Grant",
			"S" => "Florida Education Finance Program (FEFP) Reading Allocation",
			"T" => "Race to the Top (ARRA)",
			"U" => "Education Jobs Fund"
		);

		/*
		Load default values
		 */
		foreach($insert_values as $code=>$title)
		{
			$new = new FloridaFundingCodes;
			$new
				->setCode($code)
				->setTitle($title)
				->persist();
		}
	}

Database::commit();
return true;
?>
