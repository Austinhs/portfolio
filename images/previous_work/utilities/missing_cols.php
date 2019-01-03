<?php
	require('../Warehouse.php');

	$fields_RET = DBGet(DBQuery("SELECT ID,TITLE,TYPE FROM CUSTOM_FIELDS cf WHERE TYPE NOT IN ('log','holder') AND EXISTS (select '' from student_field_categories c where c.id=cf.category_id ) AND NOT EXISTS (select '' from student_field_categories c where c.id=cf.category_id and c.HAS_FORM_RECORDS='Y')"));

	$cols = db_properties('STUDENTS');

	foreach($fields_RET as $field)
	{
		if(!$cols['CUSTOM_'.$field['ID']])
		{
			echo $field['ID'].' - '.$field['TITLE'].' - '.$field['TYPE'].'<BR>';
			switch($field['TYPE'])
			{
				case 'numeric':
					DBQuery("alter table students add CUSTOM_".$field['ID'].' numeric');
				break;
				case 'text':
				case 'select_one':
				case 'select':
					DBQuery("alter table students add CUSTOM_".$field['ID'].' varchar(255)');
				break;
				case 'textarea':
				case 'multiple':
					DBQuery("alter table students add CUSTOM_".$field['ID'].' text');
				break;
				case 'radio':
					DBQuery("alter table students add CUSTOM_".$field['ID'].' varchar(1)');
				break;
				case 'date':
					DBQuery("alter table students add CUSTOM_".$field['ID'].' date');
				break;
			}
		}
	}

?>