<?php

$sql = Database::preprocess('
	INSERT INTO permission (
		id,
		profile_id,
		"key"
	)  
	SELECT
		{{next:permission_seq}},
		profile_id,
		\'SIS:PrintReferralLetters\'
	FROM
		permission
	WHERE
		"key" = \'Discipline/Referrals.php:can_edit\' AND
		NOT EXISTS (
			SELECT
				1
			FROM
				permission p2
			WHERE
				p2.profile_id = permission.profile_id AND
				p2."key" = \'SIS:PrintReferralLetters\'
		)
');

Database::query($sql);