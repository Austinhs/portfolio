<?php

// We are basing new permissions off the keys on the left side
// The keys on the right side are the new permissions
$add_permissions = [
	'SISUser:users|password:can_view'       => 'SISUser:users|force_password_change:can_view',
	'SISUser:users|password:can_edit'       => 'SISUser:users|force_password_change:can_edit',
	'SISStudent:students|password:can_view' => 'SISStudent:students|force_password_change:can_view',
	'SISStudent:students|password:can_edit' => 'SISStudent:students|force_password_change:can_edit',
];

foreach ($add_permissions as $old => $new) {
		$query = "
			INSERT INTO permission (
				profile_id,
				\"key\"
			)
			SELECT
				profile_id,
				:new
			FROM
				permission
			WHERE
				\"key\" = :old AND
				NOT EXISTS(
					SELECT
						1
					FROM
						permission p2
					WHERE
						p2.profile_id = permission.profile_id AND
						p2.\"key\" = :new
				)
		";

		$params = [
			'old' => $old,
			'new' => $new,
		];

		Database::query($query, $params);
}
