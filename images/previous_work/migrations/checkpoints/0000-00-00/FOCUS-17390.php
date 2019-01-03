<?php

if (Database::columnExists("grad_subjects", "request_group")) {
	$max_RET     = Database::get("SELECT MAX(request_group) as max FROM grad_subjects");
	$max         = (!empty($max_RET[0]['MAX'])) ? $max_RET[0]['MAX'] : 0;
	$short_names = ['CTE', 'PF'];

	foreach ($short_names as $short_name) {
		$max++;
		Database::query(
			"UPDATE grad_subjects SET request_group = :request_group WHERE short_name  = :short_name",
			[
				'request_group' => $max,
				'short_name'    => $short_name
			]
		);
	}
}
