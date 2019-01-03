<?php
	require_once('../Warehouse.php');

	if (User('PROFILE') != 'admin') {
		die();
	}

	$current_RET = DBGet(DBQuery("SELECT COURSE_PERIOD_ID,FILLED_SEATS,TITLE FROM COURSE_PERIODS WHERE SYEAR='".UserSyear()."'"),null,array('COURSE_PERIOD_ID'));
	$extra = array(
			'SELECT_ONLY'=>'COUNT(*) AS COUNT,s2.COURSE_PERIOD_ID',
			'FROM'=>',SCHEDULE s2',
			'WHERE'=>" AND s2.STUDENT_ID=ssm.STUDENT_ID AND s2.SYEAR=ssm.SYEAR AND (s2.END_DATE IS NULL OR s2.END_DATE>'".DBDate('postgres')."')",
			'GROUP'=>' s2.COURSE_PERIOD_ID',
		);
	$extra['DEBUG'] = true;
	$scheduled_RET = GetStuList($extra);

	$count = 0;
	foreach($scheduled_RET as $section)
	{
		if($section['COUNT']!=$current_RET[$section['COURSE_PERIOD_ID']][1]['FILLED_SEATS'])
		{
			DBQuery("UPDATE COURSE_PERIODS SET FILLED_SEATS='".$section['COUNT']."' WHERE COURSE_PERIOD_ID='".$section['COURSE_PERIOD_ID']."'");
			$count++;
		}
	}

	echo '<h3>Done. '.$count.' sections updated with new filled seat counts.</h3>';
?>