<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$adult_fees = array(
	"'AA'" =>'AA - Fee Waived: Honorably Discharged Veterans (US Armed Forces, US Reserve Forces, National Guard)',
	"'B'"  =>'B - Fee Waived: Tuition waiver for combat decoration superior in precedence.',
	"'BB'" =>'BB - Fee Waived: Honorably Discharged Veterans (US State Department Veteran Affairs)',
	"'C'"  =>'C - Fee Exempt (Any K-12 student who is co-enrolled in the adult high school program)',
	"'CC'" =>'CC - Fee Waived: Active Duty Members of Armed Forces',
	"'D'"  =>'D - Fee Deferred',
	"'H'"  =>'H - Fee Waived: Out of State Fee Waiver for students who are undocumented for federal immigration purposes.',
	"'I'"  =>'I - Fee Exempt (dependent of a deceased or disabled veteran)',
	"'J'"  =>'J - Fee Waiver (dependent of a deceased special risk member)',
	"'K'"  =>'K - Fee Exempt (enrolled in approved registered apprenticeship program)',
	"'M'"  =>'M - Fee Exempt (Minor in custody of DCF. Exempt until age 28.)',
	"'N'"  =>'N - Fee Exempt (enrolled in employment & training prgm under welfare trans.)',
	"'O'"  =>'O - Fee Exempt: Custody of relative through the Department of Children and Families (DCF) or adoption from DCF',
	"'P'"  =>'P - Fee Exempt (lacks a fixed, regular and adequate nighttime residence)',
	"'Q'"  =>'Q - Fee Exempt (enrolled in dual-enroll or early admission - taken BSE)',
	"'R'"  =>'R - Fee Required',
	"'T'"  =>'T - Fee Waived (victim of wrongful conviction)',
	"'V'"  =>'V - Fee Required (A residency determination was not made for this student)',
	"'W'"  =>'W - Fee Waived (by school board)',
	"'Z'"  =>'Z - Fee not Required: Enrollment not state fundable',
	"'A'"  =>'A - (Code deleted from DOE 7/1/2016)',
	"'F'"  =>'F - (Code deleted from DOE 6/1/2012)',
	"'G'"  =>'G - (Code deleted from DOE 6/1/2012)',
	"'L'"  =>'L - (Code deleted from DOE 6/1/2011)',
	"'S'"  =>'S - (Code deleted from DOE 6/1/2011)',
);

if(!Database::tableExists('ps_fa_fee_status')) {
	Database::query('
		create table ps_fa_fee_status (
			id bigint,
			deleted bigint,
			code varchar(255),
			text varchar(255)
		)
	');
}

if(!Database::sequenceExists('ps_fa_fee_status_seq')) {
	Database::createSequence('ps_fa_fee_status_seq');
}

if(!Database::columnExists('gl_ar_funding_source', 'adult_fee_status')) {
	Database::createColumn('gl_ar_funding_source', 'adult_fee_status', 'varchar(255)');
}

$next_id = Database::nextSql('ps_fa_fee_status_seq');

foreach($adult_fees as $code => $text) {
	Database::query( "
		INSERT INTO ps_fa_fee_status (id, code, text) VALUES
			(
				{$next_id},
				{$code},
				'{$text}'
			)
	");
}
