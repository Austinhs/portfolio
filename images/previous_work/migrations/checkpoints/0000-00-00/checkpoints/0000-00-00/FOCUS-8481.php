<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

if(!Database::tableExists(PsDistrictFeeTemplates::$table)) {
	$query = "
		CREATE TABLE
			" . PsDistrictFeeTemplates::$table . "
		(
			ID bigint,
			GROUP_ID bigint,
			DELETED bigint,
			TITLE varchar(255),
			SYEAR bigint
		)
	";

	Database::query($query);
}

if(!Database::tableExists(PsDistrictFeeTemplatesJoins::$table)) {
	$query = "
		CREATE TABLE
			" . PsDistrictFeeTemplatesJoins::$table . "
		(
			ID bigint,
			TEMPLATE_ID bigint,
			DELETED bigint,
			PROGRAM_ID bigint,
			SYEAR bigint
		)
	";

	Database::query($query);
}

$permissions = [
	"FeeTemplates",
	"IndividualFees",
	"FeeReport",
	"QuoteCourse",
	"MassBilling",
	"OneTimeFees",
	"CourseFeeGroups",
	"CourseFees"
];

$sis = [
	'view',
	'edit'
];

foreach($permissions as $permission) {
	foreach($sis as $special) {
		$string = "Billing/{$permission}.php:can_{$special}";
		$query = "
			UPDATE
				permission
			SET
				\"key\" = 'Ps{$string}'
			WHERE
				\"key\" = '{$string}'
			AND
				NOT EXISTS
				(
					SELECT
						''
					FROM
						permission p2
					WHERE
						\"key\" = 'Ps{$string}'
				)
		";

		Database::query($query);
	}
}
if(!Database::columnExists(PsFeeGroups::$table, 'district_group_id')) {
	Database::createColumn(PsFeeGroups::$table, 'district_group_id', 'bigint');
}

if(!Database::columnExists(PsFeeTemplates::$table, 'district')) {
	Database::createColumn(PsFeeTemplates::$table, 'district', 'bigint');
}

if(!Database::columnExists(PsFeeTemplatesJoins::$table, 'district')) {
	Database::createColumn(PsFeeTemplatesJoins::$table, 'district', 'bigint');
}

if(!Database::columnExists(PsFeeGroups::$table, 'district_join_id')) {
	Database::createColumn(PsFeeGroups::$table, 'district_join_id', 'bigint');
}

if(!Database::columnExists(PsFeeTemplates::$table, 'district_join_id')) {
	Database::createColumn(PsFeeTemplates::$table, 'district_join_id', 'bigint');
}

if(!Database::columnExists(PsFeeTemplatesJoins::$table, 'district_join_id')) {
	Database::createColumn(PsFeeTemplatesJoins::$table, 'district_join_id', 'bigint');
}

if(!Database::columnExists(PsFeeTemplatesJoins::$table, 'course_period_id')) {
        Database::createColumn(PsFeeTemplatesJoins::$table, 'course_period_id', 'bigint');
}

if(!Database::columnExists(PsFees::$table, 'term_limited')) {
	Database::createColumn(PsFees::$table, 'term_limited', 'bigint');
}

if(!Database::columnExists(PsFees::$table, 'district')) {
	Database::createColumn(PsFees::$table, 'district', 'bigint');
}

if(!Database::columnExists(PsFees::$table, 'inactive')) {
	Database::createColumn(PsFees::$table, 'inactive', 'bigint');
}
