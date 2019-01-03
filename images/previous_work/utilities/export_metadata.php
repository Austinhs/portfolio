<?php
require_once("../Warehouse.php");

$tables = db_tables();

$table_exceptions = array('EXTERNAL_PROGRAMS','ALL_MARKING_PERIODS','ALL_MPS');
//7.2
//$table_exceptions = array_merge($table_exceptions,array('SCHOOL_FOLDER','REFERRAL_ACTIONS','REFERRAL_CODES','STUDENT_REPORT_CARD_GRADES_CHANGE_REQUESTS','SCHOOL_CHOICE_APPLICATION_FIELDS','SCHOOL_CHOICE_PRIORITY_RANKINGS'));

$ignore_prefixes = array('gl_','mdl_','sss_','iep_','student_form_records_','user_form_records_','pg_','studentscustom_');

foreach($tables as $table=>$cols)
{
	foreach($ignore_prefixes as $pre)
	{
		if(strtolower(substr($table,0,strlen($pre)))==$pre)
			continue 2;
	}

	if(in_array(strtoupper($table),$table_exceptions))
		continue;

	$metadata[$table] = db_properties($table);
}

$primary_keys = db_primary_keys();
foreach($primary_keys as $table=>$info)
{
	foreach($ignore_prefixes as $pre)
	{
		if(strtolower(substr($table,0,strlen($pre)))==$pre)
			unset($primary_keys[$table]);
	}
}
$metadata['pkeys'] = $primary_keys;
$sequences = db_sequences();
foreach($sequences as $table=>$info)
{
	foreach($ignore_prefixes as $pre)
	{
		if(strtolower(substr($table,0,strlen($pre)))==$pre)
			unset($sequences[$table]);
	}
}

$metadata['sequences'] = $sequences;

$indicies = db_indicies();
foreach($indicies as $table=>$info)
{
	foreach($ignore_prefixes as $pre)
	{
		if(strtolower(substr($table,0,strlen($pre)))==$pre)
			unset($indicies[$table]);
	}
}
$metadata['indicies'] = $indicies;


echo str_replace("'","\'",serialize($metadata));



?>
