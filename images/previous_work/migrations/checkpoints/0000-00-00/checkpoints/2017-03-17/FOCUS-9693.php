<?php
if(Database::$type === 'mssql'){

	Database::begin();

	Database::query("
		DELETE FROM
			scheduling_lunch_rule
		WHERE
			NOT EXISTS(
				SELECT
					1
				FROM
					scheduling_lunch
					INNER JOIN scheduling_lunch_rule_detail ON scheduling_lunch_rule_detail.lunch_rule_id = scheduling_lunch_rule.id
				WHERE
					scheduling_lunch_rule.lunch_id = scheduling_lunch.id
			)
	");

	Database::query("
		ALTER SEQUENCE
			scheduling_lunch_seq
		RESTART WITH 1
	");
	Database::query("
		ALTER SEQUENCE
			scheduling_lunch_rule_seq
		RESTART WITH 1
	");
	Database::query("
		ALTER SEQUENCE
			scheduling_lunch_rule_detail_seq
		RESTART WITH 1
	");

	$sql = Database::preprocess("
		UPDATE
			scheduling_lunch
		SET
			id = {{next:scheduling_lunch_seq}}
		OUTPUT
			inserted.id AS new_id,
			deleted.id  AS old_id
	");

	$rows = Database::get($sql);

	foreach($rows as $row){

		$sql2 = Database::preprocess("
			UPDATE
				scheduling_lunch_rule
			SET
				lunch_id = {$row['NEW_ID']},
				id = {{next:scheduling_lunch_rule_seq}}
			OUTPUT
				inserted.id AS new_id2,
				deleted.id AS old_id2
			WHERE
				lunch_id = {$row['OLD_ID']}
		");

		$rows2 = Database::get($sql2);

		foreach($rows2 as $row2){

			$sql3 = Database::preprocess("
			UPDATE
				scheduling_lunch_rule_detail
			SET
				LUNCH_RULE_ID = {$row2['NEW_ID2']},
				id = {{next:scheduling_lunch_rule_detail_seq}}
			OUTPUT
				inserted.id AS new_id3,
				deleted.id AS old_id3
			WHERE
				lunch_rule_id = {$row2['OLD_ID2']}
			");

			$rows3 = Database::get($sql3);
		}
	}

	try{
		Database::commit();
		$status = 'Migration Was Successful';
	}
	catch(Exception $e) {
		$status = '<br>Migration Has Failed'.$e;
		Database::rollback();
	}
	//echo $status;
}
