<?php
/*
 * Prior to this branch, start and end dates would show up if the current
 * school was post-secondary. Since start and end dates are now via
 * system preference, this migration simply goes through each post secondary
 * school and sets the syspref to display the start/end date fields.
 */

// Get all schools that are post secondary and see if the syspref isn't already there.
$post_secondary_schools_RET = Database::get("
	SELECT
		pg.school_id,
		pg.syear,
		new_pg.school_id AS existing_pref_id
	FROM
		program_config AS pg
		LEFT JOIN program_config AS new_pg
			ON (new_pg.title='SHOW_START_END_DATES_ON_FINAL_GRADES'
				AND new_pg.program='school_prefs'
				AND new_pg.school_id=pg.school_id
				AND new_pg.syear=pg.syear)
	WHERE
		pg.title='POST_SECONDARY'
		AND pg.program='school_prefs'
		AND pg.value='Y'
");

foreach ($post_secondary_schools_RET as $row) {
	$school_id = $row['SCHOOL_ID'];
	$syear     = $row['SYEAR'];
	$exists    = !empty($row['EXISTING_PREF_ID']);

	if (!$exists) {
		Database::query($sql = "
			INSERT INTO program_config(school_id, syear, program, title, value)
			VALUES(
				{$school_id},
				{$syear},
				'school_prefs',
				'SHOW_START_END_DATES_ON_FINAL_GRADES',
				'Y'
			)
		");

		echo str_replace("\n", "", $sql) . "\n";
	}
	
}

