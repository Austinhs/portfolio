<?php
if (Database::$type === 'mssql') {

	$bad_ids = Database::get("select cast(id as varchar) as id from referral_actions where id < 1");

	if(!empty($bad_ids)) {
		$new_id = 1;

		foreach ($bad_ids as $id_ret) {
			$bad_id = $id_ret['ID'];

			Database::query("update referral_actions set id={$new_id} where id={$bad_id}");
			Database::query("update referral_codes set actions = REPLACE(actions, '\"{$bad_id}\"', '\"{$new_id}\"')");

			++$new_id;
		}

		$referral_codes_reset = Database::get("select cast(id as varchar) as id from referral_codes");
		$new_id2 = 1;
		foreach($referral_codes_reset as $rcr){
			$gross_id = $rcr['ID'];
			Database::query("update referral_codes set id = {$new_id2} where id={$gross_id}");
			++$new_id2;
		}

		$restart = [
			'referral_actions' => 'referral_actions_seq',
			'referral_codes'   => 'referral_codes_seq'
		];

		foreach($restart as $table => $seq) {
			$id_rows = Database::get("SELECT (MAX(id) + 1) AS id FROM {$table}");
			$id_row  = reset($id_rows);

			if(empty($id_row)) {
				$id = 1;
			}
			else {
				$id = intval($id_row['ID']);
			}

			Database::query("alter sequence {$seq} restart with {$id}");
		}
	}

}