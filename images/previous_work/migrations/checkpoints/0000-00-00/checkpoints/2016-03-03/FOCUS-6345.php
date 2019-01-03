<?php
if (Database::$type === 'mssql') {
	$rows = Database::get("
		WITH max AS (
			SELECT
				MAX(id) AS seq
			FROM
				referral_codes
		)
		SELECT
			(
				CASE WHEN seq >= 0 THEN seq + 1 ELSE 1 END
			) AS seq
		FROM
			max
	");

	$row = reset($rows);

	if(!empty($row)) {
		$seq = intval($row['SEQ']);
		$alter_sql = "
			ALTER SEQUENCE referral_codes_seq RESTART WITH ".$seq."
		";

		Database::query($alter_sql);
	}
}