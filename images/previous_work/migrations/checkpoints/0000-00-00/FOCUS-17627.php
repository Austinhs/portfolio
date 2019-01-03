<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$sup = Supplements::getAll();
$cod = [];

foreach ($sup as $s) {
	if ($s->getCode() != "") {
		$cod[$s->getCode()] = true;
	}
}

$doe = [
	"1" => "Assignment to a School in the Bottom Two Categories of the School Improvement System",
	"2" => "Certification and Teaching in Critical Teacher Shortage Areas",
	"3" => "Assignment of Additional Academic Responsibilities",
	"4" => "Instruction in a Course that Led to a CAPE Industry Certification Bonus",
	"A" => "Athletic",
	"B" => "Academic",
	"E" => "Inservice Stipends",
	"F" => "Extended Day",
	"G" => "Other",
	"H" => "Florida Excellent Teaching Program Bonus",
	"I" => "Florida School Recognition Program",
	"J" => "Performance Bonus",
	"K" => "Advanced Placement Instruction Bonus",
	"N" => "Teacher Retention in an Area of Critical State Concern Bonus",
	"O" => "Teacher Recruitment in an Area of Critical State Concern Bonus",
	"P" => "Teacher (Instructional Personnel) Retention Bonus",
	"Q" => "International Baccalaureate Instruction Bonus",
	"R" => "Teacher Recruitment Bonus (Critical Needs)",
	"S" => "Sick Leave Buy Back",
	"T" => "Terminal Pay",
	"U" => "In-Kind Compensation",
	"V" => "Sabbatical Leave Pay",
	"W" => "AICE",
	"X" => "MAP/Star Performance Pay",
	"Y" => "Advanced Degree in Certification",
	"Z" => "Title 1 Supplements"
];

foreach ($doe as $k => $v) {
	if (!isset($cod[$k])) {
		$tmp = (new Supplements())
			->setStateReportingCode($k)
			->setStatus("A")
			->setCode($k)
			->setTitle($v)
			->persist();
	}
}

return true;
