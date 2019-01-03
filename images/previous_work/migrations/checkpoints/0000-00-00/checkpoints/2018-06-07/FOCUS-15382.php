<?php

// Check if florida reports is installed
$state_name = null;
if(!empty($GLOBALS['_FOCUS']['config']['state_name'])) {
	$state_name = strtolower($GLOBALS['_FOCUS']['config']['state_name']);
}
if($state_name !== 'florida'){
	return;
}

$field = SISStudent::getFieldByColumnName('custom_200000237');
if(empty($field)) {
	throw new Exception('The Industry Certifications logging field does not exist.');
}

$field_id    = $field['id'];
$log_columns = SISStudent::getLogColumns($field_id);
$column      = null;

foreach($log_columns as $log_column_id => $log_column) {
	if($log_column['column_name'] == 'LOG_FIELD6') {
		$column = new CustomFieldLogColumn($log_column_id);
	}
}

if(empty($column)) {
	throw new Exception('The Industry Certification Identifier field does not exist.');
}

$sql = "
	SELECT
		id,
		cert_number AS code,
		CONCAT(cert_number, ' - ', description) AS label,
		(CASE WHEN SYEAR = {SYEAR} THEN 0 ELSE 1 END) AS inactive
	FROM
		florida_industry_certifications
";

$column
	->setOptionQuery($sql)
	->persist();
