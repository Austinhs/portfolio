<?php

// Insert permissions to view/edit general and enrollment fields
$fields = [
	'student_enrollment' => SISStudent::getFields('enrollment'),
	'student_general'    => SISStudent::getFields('general'),
	'user_enrollment'    => SISUser::getFields('enrollment'),
	'user_general'       => SISUser::getFields('general')
];

$permissions = [
	'student_enrollment' => [
		'view' => 'SIS:ViewStudentEnrollment',
		'edit' => 'SIS:EditStudentEnrollment'
	],

	'student_general' => [
		'view' => 'Students/Student.php:can_view',
		'edit' => 'Students/Student.php:can_edit'
	],

	'user_enrollment' => [
		'view' => 'Users/User.php:can_view',
		'edit' => 'SIS:AdminUserEnrollment'
	],

	'user_general' => [
		'view' => 'Users/User.php:can_view',
		'edit' => 'Users/User.php:can_edit'
	]
];

$new_permissions = [];

foreach($fields as $group => $tmp_fields) {
	$tmp_field1 = reset($tmp_fields);
	$tmp_class  = $tmp_field1['source_class'];

	foreach($permissions[$group] as $type => $key) {
		$where = [
			"\"key\" = :key",
		];

		$params = [
			'key' => $key
		];

		$all_sql = "
			SELECT DISTINCT
				profile_id, \"key\"
			FROM
				permission
			WHERE
				\"key\" LIKE '{$tmp_class}:%:can_{$type}'
		";

		$all = array_flip(array_map(function($row) {
			return "{$row['KEY']}~~~{$row['PROFILE_ID']}";
		}, Database::get($all_sql)));

		$old = Permission::getAllAndLoad($where, null, $params);

		foreach($old as $permission) {
			$profile_id = intval($permission->getProfileId());

			foreach($tmp_fields as $field_id => $field) {
				$key   = "{$tmp_class}:{$field_id}:can_{$type}";
				$check = "{$key}~~~{$profile_id}";
				$skip  = $field['alias'] === 'ssn';

				if(isset($all[$check]) || $skip) {
					continue;
				}

				$tmp_permission = new Permission();

				$tmp_permission
					->setKey($key)
					->setProfileId($profile_id);

				$new_permissions[] = $tmp_permission;
			}
		}
	}
}

if(!empty($new_permissions)) {
	Permission::insert($new_permissions);
}
