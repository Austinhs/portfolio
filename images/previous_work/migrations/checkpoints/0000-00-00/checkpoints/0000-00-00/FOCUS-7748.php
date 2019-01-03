<?php

Migrations::depend('FOCUS-11459');
Migrations::depend('FOCUS-9563');

/**
 * Description:
 * Adding custom student fields for Gainful Employment reports
 * Fields are added to the Placement category of Student Info for CTE schools
 */

// Only run the version 8 migration if we've already run the FOCUS-5468 migration
$isVersion8 = class_exists('CustomFieldObject') && Database::tableExists(CustomFieldSelectOption::$table);

$query1 = "
INSERT INTO custom_fields (ID, TYPE, SELECT_OPTIONS, CATEGORY_ID, SELECT_OPTION_CODES, APPLICATION, TITLE, LOG_FIELD1_SORT_ORDER, LOG_FIELD2_SORT_ORDER, LOG_FIELD3_SORT_ORDER, LOG_FIELD4_SORT_ORDER, LOG_FIELD5_SORT_ORDER, LOG_FIELD6_SORT_ORDER, LOG_FIELD7_SORT_ORDER, LOG_FIELD8_SORT_ORDER, LOG_FIELD9_SORT_ORDER, LOG_FIELD10_SORT_ORDER, LOG_FIELD11_SORT_ORDER, LOG_FIELD12_SORT_ORDER, LOG_FIELD13_SORT_ORDER, LOG_FIELD14_SORT_ORDER, LOG_FIELD15_SORT_ORDER, LOG_FIELD16_SORT_ORDER, LOG_FIELD17_SORT_ORDER, LOG_FIELD18_SORT_ORDER, LOG_FIELD19_SORT_ORDER, LOG_FIELD20_SORT_ORDER, LOG_FIELD21_SORT_ORDER, LOG_FIELD22_SORT_ORDER, LOG_FIELD23_SORT_ORDER, LOG_FIELD24_SORT_ORDER, LOG_FIELD25_SORT_ORDER, LOG_FIELD26_SORT_ORDER, LOG_FIELD27_SORT_ORDER, LOG_FIELD28_SORT_ORDER, LOG_FIELD29_SORT_ORDER, LOG_FIELD30_SORT_ORDER)
values('2015120008', 'select',
'Undergraduate certificate or Diploma program [01]
Associate''s degree [02]
Bachelor''s degree [03]
Post baccalaureate certificate [04]
Master''s degree [05]
Doctoral degree [06]
First professional degree [07]
Graduate / Professional certificate [08]', '2015073101',
'01
02
03
04
05
06
07
08', 'Y', 'Credential Level', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30')
";

$query2 = "
INSERT INTO custom_fields (ID, TYPE, SELECT_OPTIONS, CATEGORY_ID, SELECT_OPTION_CODES, TITLE, LOG_FIELD1_SORT_ORDER, LOG_FIELD2_SORT_ORDER, LOG_FIELD3_SORT_ORDER, LOG_FIELD4_SORT_ORDER, LOG_FIELD5_SORT_ORDER, LOG_FIELD6_SORT_ORDER, LOG_FIELD7_SORT_ORDER, LOG_FIELD8_SORT_ORDER, LOG_FIELD9_SORT_ORDER, LOG_FIELD10_SORT_ORDER, LOG_FIELD11_SORT_ORDER, LOG_FIELD12_SORT_ORDER, LOG_FIELD13_SORT_ORDER, LOG_FIELD14_SORT_ORDER, LOG_FIELD15_SORT_ORDER, LOG_FIELD16_SORT_ORDER, LOG_FIELD17_SORT_ORDER, LOG_FIELD18_SORT_ORDER, LOG_FIELD19_SORT_ORDER, LOG_FIELD20_SORT_ORDER, LOG_FIELD21_SORT_ORDER, LOG_FIELD22_SORT_ORDER, LOG_FIELD23_SORT_ORDER, LOG_FIELD24_SORT_ORDER, LOG_FIELD25_SORT_ORDER, LOG_FIELD26_SORT_ORDER, LOG_FIELD27_SORT_ORDER, LOG_FIELD28_SORT_ORDER, LOG_FIELD29_SORT_ORDER, LOG_FIELD30_SORT_ORDER)
	values('2015120009', 'select',
'Graduated [G]
Withdrew [W]
Enrolled [E]', '2015073101',
'G
W
E', 'Program Attendance Status During Award Year', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30')
";

$query3 = "
INSERT INTO custom_fields (ID, TYPE, SELECT_OPTIONS, CATEGORY_ID, SELECT_OPTION_CODES, TITLE, LOG_FIELD1_SORT_ORDER, LOG_FIELD2_SORT_ORDER, LOG_FIELD3_SORT_ORDER, LOG_FIELD4_SORT_ORDER, LOG_FIELD5_SORT_ORDER, LOG_FIELD6_SORT_ORDER, LOG_FIELD7_SORT_ORDER, LOG_FIELD8_SORT_ORDER, LOG_FIELD9_SORT_ORDER, LOG_FIELD10_SORT_ORDER, LOG_FIELD11_SORT_ORDER, LOG_FIELD12_SORT_ORDER, LOG_FIELD13_SORT_ORDER, LOG_FIELD14_SORT_ORDER, LOG_FIELD15_SORT_ORDER, LOG_FIELD16_SORT_ORDER, LOG_FIELD17_SORT_ORDER, LOG_FIELD18_SORT_ORDER, LOG_FIELD19_SORT_ORDER, LOG_FIELD20_SORT_ORDER, LOG_FIELD21_SORT_ORDER, LOG_FIELD22_SORT_ORDER, LOG_FIELD23_SORT_ORDER, LOG_FIELD24_SORT_ORDER, LOG_FIELD25_SORT_ORDER, LOG_FIELD26_SORT_ORDER, LOG_FIELD27_SORT_ORDER, LOG_FIELD28_SORT_ORDER, LOG_FIELD29_SORT_ORDER, LOG_FIELD30_SORT_ORDER)
	values('2015120010', 'select',
'Full-Time [F]
Three-Quarter Time [Q]
Half-Time [H]
Less Than Half-Time [L]', '2015073101',
'F
Q
H
L', 'Student''s Enrollment Status as of the 1st Day of Enrollment in Program', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30')
";

$query4 = "
INSERT INTO custom_fields (ID, TYPE, CATEGORY_ID, TITLE, LOG_FIELD1_SORT_ORDER, LOG_FIELD2_SORT_ORDER, LOG_FIELD3_SORT_ORDER, LOG_FIELD4_SORT_ORDER, LOG_FIELD5_SORT_ORDER, LOG_FIELD6_SORT_ORDER, LOG_FIELD7_SORT_ORDER, LOG_FIELD8_SORT_ORDER, LOG_FIELD9_SORT_ORDER, LOG_FIELD10_SORT_ORDER, LOG_FIELD11_SORT_ORDER, LOG_FIELD12_SORT_ORDER, LOG_FIELD13_SORT_ORDER, LOG_FIELD14_SORT_ORDER, LOG_FIELD15_SORT_ORDER, LOG_FIELD16_SORT_ORDER, LOG_FIELD17_SORT_ORDER, LOG_FIELD18_SORT_ORDER, LOG_FIELD19_SORT_ORDER, LOG_FIELD20_SORT_ORDER, LOG_FIELD21_SORT_ORDER, LOG_FIELD22_SORT_ORDER, LOG_FIELD23_SORT_ORDER, LOG_FIELD24_SORT_ORDER, LOG_FIELD25_SORT_ORDER, LOG_FIELD26_SORT_ORDER, LOG_FIELD27_SORT_ORDER, LOG_FIELD28_SORT_ORDER, LOG_FIELD29_SORT_ORDER, LOG_FIELD30_SORT_ORDER)
	values('2015120011', 'numeric', '2015073101', 'Private Loan Amounts', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30')
";

$query5 = "
INSERT INTO custom_fields (ID, TYPE, CATEGORY_ID, TITLE, LOG_FIELD1_SORT_ORDER, LOG_FIELD2_SORT_ORDER, LOG_FIELD3_SORT_ORDER, LOG_FIELD4_SORT_ORDER, LOG_FIELD5_SORT_ORDER, LOG_FIELD6_SORT_ORDER, LOG_FIELD7_SORT_ORDER, LOG_FIELD8_SORT_ORDER, LOG_FIELD9_SORT_ORDER, LOG_FIELD10_SORT_ORDER, LOG_FIELD11_SORT_ORDER, LOG_FIELD12_SORT_ORDER, LOG_FIELD13_SORT_ORDER, LOG_FIELD14_SORT_ORDER, LOG_FIELD15_SORT_ORDER, LOG_FIELD16_SORT_ORDER, LOG_FIELD17_SORT_ORDER, LOG_FIELD18_SORT_ORDER, LOG_FIELD19_SORT_ORDER, LOG_FIELD20_SORT_ORDER, LOG_FIELD21_SORT_ORDER, LOG_FIELD22_SORT_ORDER, LOG_FIELD23_SORT_ORDER, LOG_FIELD24_SORT_ORDER, LOG_FIELD25_SORT_ORDER, LOG_FIELD26_SORT_ORDER, LOG_FIELD27_SORT_ORDER, LOG_FIELD28_SORT_ORDER, LOG_FIELD29_SORT_ORDER, LOG_FIELD30_SORT_ORDER)
VALUES('2015120012', 'radio', '2015073101', 'Medical or Dental Internship or Residency', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30')
";

$fields = [
	'2015120008' => [
		'8type' => 'select',
		'dataType' => 'VARCHAR',
		'length' => 255,
		'7query' => $query1,
		'8label' => 'Credential Level',
		'options' => [
			'01' => 'Undergraduate certificate or Diploma program [01]',
			'02' => 'Associate\'s degree [02]',
			'03' => 'Bachelor\'s degree [03]',
			'04' => 'Post baccalaureate certificate [04]',
			'05' => 'Master\'s degree [05]',
			'06' => 'Doctoral degree [06]',
			'07' => 'First professional degree [07]',
			'08' => 'Graduate / Professional certificate [08]'
		]
	],
	'2015120009' => [
		'8type' => 'select',
		'dataType' => 'VARCHAR',
		'length' => 255,
		'7query' => $query2,
		'8label' => 'Program Attendance Status During Award Year',
		'options' => [
			'G' => 'Graduated [G]',
			'W' => 'Withdrew [W]',
			'E' => 'Enrolled [E]'
		]
	],
	'2015120010' => [
		'8type' => 'select',
		'dataType' => 'VARCHAR',
		'length' => 255,
		'7query' => $query3,
		'8label' => 'Student\'s Enrollment Status as of the 1st Day of Enrollment in Program',
		'options' => [
			'F' => 'Full-Time [F]',
			'Q' => 'Three-Quarter Time [Q]',
			'H' => 'Half-Time [H]',
			'L' => 'Less Than Half-Time [L]'
		]
	],
	'2015120011' => [
		'8type' => 'numeric',
		'dataType' => 'VARCHAR',
		'length' => 255,
		'7query' => $query4,
		'8label' => 'Private Loan Amounts'
	],
	'2015120012' => [
		'8type' => 'checkbox',
		'dataType' => 'VARCHAR',
		'length' => 1,
		'7query' => $query5,
		'8label' => 'Medical or Dental Internship or Residency'
	]
];

if (!$isVersion8) {
	// Version 7.* Migration

	foreach ($fields as $columnName => $columnParams) {

		if (!Database::columnExists('students', "custom_{$columnName}")) {
			$columnType   = $columnParams['dataType'];
			$columnLength = $columnParams['length'];
			$columnQuery  = $columnParams['7query'];

			$sql              = "SELECT id FROM custom_fields WHERE category_id = 2015073101 AND id = {$columnName}";
			$fieldExistsQuery = Database::get($sql);
			$fieldExists      = !empty($fieldExistsQuery) ? true : false;

			if (!$fieldExists) {
				Database::query($columnQuery);
				Database::createColumn('students', "custom_{$columnName}", $columnType, $columnLength);
			}
		}
	}
} else {
	// Version 8.* Migration
	$categoryLegacyId = 2015073101;
	$category = CustomFieldCategory::getOne("legacy_id = :legacy_id", null, ['legacy_id' => $categoryLegacyId]);
	$sortOrder = 7;
	// Add Custom Fields
	foreach ($fields as $fieldName => $fieldParams) {
		$legacyFieldId = $fieldName;
		$fieldType = $fieldParams['8type'];
		$fieldLabel = $fieldParams['8label'];
		$fieldOptions = $fieldParams['options'];
		$sortOrder += 1;
		$field = CustomField::getOne('legacy_id = :legacy_id', null, ['legacy_id' => $legacyFieldId]);
		if (empty($field)) {
			$field = new CustomField;
			$field
				->setSourceClass('SISStudent')
				->setType($fieldType)
				->setTitle($fieldLabel)
				->setColumnName("custom_{$fieldName}")
				->setAlias("custom_{$fieldName}")
				->setLegacyId($legacyFieldId);

			$field->persist();

			if(!empty($category)) {
				$fieldJoinCategory = new CustomFieldJoinCategory;
				$fieldJoinCategory
					->setFieldId($field->getId())
					->setCategoryId($category->getId())
					->setSortOrder($sortOrder);

				$fieldJoinCategory->persist();
			}

			if ($fieldType === 'select') {
				foreach ($fieldOptions as $key => $value) {
					$option = new CustomFieldSelectOption;
					$option
						->setSourceClass('CustomField')
						->setSourceId($field->getId())
						->setCode($key)
						->setLabel($value);

					$option->persist();
				}
			}
		}
	}
}
