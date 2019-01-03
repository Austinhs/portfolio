<?php

$sql = '
	INSERT INTO permission(profile_id, "key")
	SELECT
		profile_id,
		\'SIS:OverwriteUploadedPhotos\' AS "key"
	FROM
		permission p1
	WHERE
		"key" = \'SIS:UploadPhotosToAllSchools\' AND
		NOT EXISTS(
			SELECT
				1
			FROM
				permission p2
			WHERE
				p1.profile_id = p2.profile_id AND
				"key" = \'SIS:OverwriteUploadedPhotos\'
		)
';

if(Database::tableExists('permission')) {
	Database::query($sql);
}