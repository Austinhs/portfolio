<?php

// If a user has permission to Edit other school's referrals, then add permission to View other school's referrals
Database::query("
	INSERT INTO permission (profile_id, \"key\")
	SELECT
		profile_id,
		'SIS:ViewOtherReferrals'
	FROM
		permission p1
	WHERE
		\"key\" = 'SIS:EditOtherReferrals' AND
		NOT EXISTS (
			SELECT
				1
			FROM
				permission
			WHERE
				\"key\"    = 'SIS:ViewOtherReferrals' AND
				profile_id = p1.profile_id
		)
");
