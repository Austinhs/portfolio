<?php
//Check for Finance
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

//Check if its FL state
if(Facility::getDistrictFacility()->getState() != 'FL') {
  return false;
}

if(!Database::columnExists('gl_hr_benefit_types', 'fl_benefit_code')) {
	//making this 3 for scalability incase they run out of the alphabit.
  Database::createColumn('gl_hr_benefit_types', 'fl_benefit_code', 'char', '3');

  Database::query(
    "CREATE TABLE gl_benefit_codes (
      id BIGINT PRIMARY KEY NOT NULL,
      code char(3),
      title varchar(255),
      state char(2)
    )
  ");

  $benefits = [
    'A' => 'Health and Hospitalization',
    'B' => 'Life Insurance',
    'C' => 'Social Security',
    'D' => 'Florida Retirement System',
    'E' => 'Commercial or Mutual Insurance Annuity Plan',
    'F' => 'Unemployment Compensation',
    'G' => 'Workers Compensation',
    'K' => 'Cafeteria Plan',
    'L' => 'Other',
    'M' => 'Medicare',
    'N' => 'Cafeteria Plan - Administrative Costs'
  ];

	$insert = [];

	foreach($benefits as $key => $benefit) {
		$insert[] = [
			'code' => $key,
			'title' => $benefit,
			'state' => 'FL'
		];
	}

  Database::insert(
    BenefitCodes::$table,
    BenefitCodes::$sequence,
    array_keys($insert[0]),
    $insert
  );
}
