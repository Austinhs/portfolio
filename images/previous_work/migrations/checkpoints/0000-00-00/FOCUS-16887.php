<?php
if(!Database::columnExists('report_card_grade_scales', 'show_average_in_gradebook')){
	Database::begin();

	//Add show_average_in_gradebook column
	Database::createColumn('report_card_grade_scales', 'show_average_in_gradebook', 'varchar');

	// Find scales to update based on System Preferences
	$scale_sql = "
		SELECT
			id,
			school_id,
			syear
		FROM
			report_card_grade_scales
		WHERE
			show_average_in_gradebook IS NULL
	";

	$scales       = Database::get($scale_sql);
	$show_average = [];

	foreach($scales as $key => $scale) {
		$scales[$key]['show_average_in_gradebook'] = SystemPreferences('GRADEBOOK_RUNNING_GRADE','school_prefs',false,$scale['SCHOOL_ID'],$scale['SYEAR']);

		if(SystemPreferences('GRADEBOOK_RUNNING_GRADE','school_prefs',false,$scale['SCHOOL_ID'],$scale['SYEAR']) === 'Y') {
			$show_average[$scale['ID']] = $scale['ID'];
		}
	}

	$scale_ids_show_average = implode(',', $show_average);

	// Update Scales
	$update_sql = "
		UPDATE
			report_card_grade_scales
		SET
			show_average_in_gradebook =
				CASE
					WHEN id IN ({$scale_ids_show_average}) THEN 'Y'
					ELSE 'N'
				END
	";

	Database::query($update_sql);

	Database::commit();

}