<?php

include '../Warehouse.php';
$extra['SELECT'] = ',a.ADDRESS_ID';
$extra['addr'] = true;
$extra['WHERE'] .= ' AND ((a.USERNAME IS NULL AND a.ADDRESS_ID IS NOT NULL) OR s.USERNAME IS NULL)';
$_REQUEST['_search_all_schools'] = 'Y';
$students_RET = GetStuList($extra);

if(!$_REQUEST['type'])
	$_REQUEST['type'] = 'both';

foreach($students_RET as $address)
{
	if($_REQUEST['type']!='parent')
	{
		$password = generatePassword(9,8);
		if(!$students_done[$address['STUDENT_ID']])
		{
			$username = substr($address['FIRST_NAME'],0,1).$address['LAST_NAME'];
			$i = '';
			do
			{
				$student_username = str_replace("'","''",$username.$i);
				$i++;
			}
			while(count(DBGet(DBQuery("SELECT STUDENT_ID FROM STUDENTS WHERE USERNAME='".$student_username."'"))) || count(DBGet(DBQuery("SELECT '' FROM ADDRESS WHERE USERNAME='".$student_username."'"))) || count(DBGet(DBQuery("SELECT '' FROM USERS WHERE USERNAME='".$student_username."'"))));
			DBQuery("UPDATE STUDENTS SET USERNAME='$student_username',PASSWORD='$password' WHERE STUDENT_ID='".$address['STUDENT_ID']."'");
			$students_done[$address['STUDENT_ID']] = true;
			echo $student_username.'<BR>';
		}
	}

	if($_REQUEST['type']!='student')
	{
		$username = 'p.'.$address['LAST_NAME'];
		$password = generatePassword(6,4);
		$i = '';
		do
		{
			$parent_username = str_replace("'","''",$username.$i);
			$i++;
		}
		while(count(DBGet(DBQuery("SELECT STUDENT_ID FROM STUDENTS WHERE USERNAME='".$parent_username."'"))) || count(DBGet(DBQuery("SELECT '' FROM ADDRESS WHERE USERNAME='".$parent_username."'"))) || count(DBGet(DBQuery("SELECT '' FROM USERS WHERE USERNAME='".$parent_username."'"))));
		DBQuery("UPDATE ADDRESS SET USERNAME='$parent_username',PASSWORD='$password' WHERE ADDRESS_ID='".$address['ADDRESS_ID']."'");
		echo $parent_username.'<BR>';
	}
}

echo 'Done.';


function generatePassword($length=9, $strength=0)
{
	$vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz';
	if ($strength & 1) {
		$consonants .= 'BDGHJLMNPQRSTVWXZ';
	}
	if ($strength & 2) {
		$vowels .= "AEUY";
	}
	if ($strength & 4) {
		$consonants .= '23456789';
	}
	if ($strength & 8) {
		$consonants .= '@#$%';
	}

	$password = '';
	$alt = time() % 2;
	for ($i = 0; $i < $length; $i++) {
		if ($alt == 1) {
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} else {
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $password;
}


?>