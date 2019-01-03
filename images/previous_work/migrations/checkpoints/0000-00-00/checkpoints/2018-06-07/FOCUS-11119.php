<?php

Database::begin();

if($GLOBALS['ClientId'] === 7489238427) {

	$sql = "
		SELECT
			*
		FROM
			gl_element e1
		WHERE
			EXISTS (
				SELECT
					1
				FROM
					gl_element e2
				WHERE
					e1.id != e2.id AND
					e1.element_category_id = e2.element_category_id AND
					e1.code = e2.code AND
					e2.deleted IS NULL
		) AND deleted IS NULL
	";

	$data = [];
	$tmp  = Database::get($sql);
	foreach($tmp as $record) {
		$code = $record['CODE'];

		if(!isset($data[$code])) {
			$data[$code] = [];
		}

		if($record['TITLE'] === 'TBD') {
			$data[$code]['BAD_ELEMENT'] = $record;
		}
		else {
			$data[$code]['GOOD_ELEMENT'] = $record;
		}
	}

	// Duplicte the strips
	foreach($data as $code => $record) {
		$sql = "
			UPDATE
				gl_accounting_strip
			SET
				category_revenue = {$record['GOOD_ELEMENT']['ID']}
			WHERE
				category_revenue = {$record['BAD_ELEMENT']['ID']}
		";
		Database::query($sql);
	}

	if(!Database::tableExists('gl_accounting_strip_bk_dup_strips_will')) {
		$sql = "SELECT * INTO gl_accounting_strip_bk_dup_strips_will FROM gl_accounting_strip";
		Database::query($sql);
	}

	if(!Database::tableExists('gl_budget_bk_dup_strips_will')) {
		$sql = "SELECT * INTO gl_budget_bk_dup_strips_will FROM gl_budget";
		Database::query($sql);
	}

	// Delete bad journals
	$sql = "
		DELETE FROM
			gl_journals j
		WHERE
			NOT EXISTS (
				SELECT
					1
				FROM
					gl_accounting_strip s
				WHERE
					s.id = j.accounting_strip_id
		) AND
		source = 'BM Initial Revenue Budget' AND
		amount = 0
	";
	Database::query($sql);

	$categories = ElementCategory::getAllAndLoad($where);
	$where      = [];
	$compare    = [];

	foreach($categories as $category) {
		$category_name = $category->getName();
		$where[]       = "COALESCE(s1.{$category_name}, 0) = COALESCE(s2.{$category_name}, 0)";
		$compare[]     = $category_name;
	}

	$where   = implode(' AND ', $where);
	$compare = implode(', \'::\',', $compare);

	Database::query("DROP TABLE IF EXISTS gl_tmp_dup_strips");
	$sql = "
		CREATE TABLE gl_tmp_dup_strips (
			strip_id bigint,
			compare_string text
		)
	";
	Database::query($sql);

	$sql = "
		INSERT INTO gl_tmp_dup_strips (strip_id, compare_string)
		SELECT
			id, CONCAT({$compare}) AS compare_string
		FROM
			gl_accounting_strip s1
		WHERE
			EXISTS (
				SELECT
					1
				FROM
					gl_accounting_strip s2
				WHERE
					{$where} AND
					s1.id != s2.id AND
					COALESCE(s1.deleted, 0) = 0 AND
					COALESCE(s2.deleted, 0) = 0
			)
	";
	Database::query($sql);

	$dups         = Database::get("SELECT * FROM gl_tmp_dup_strips");
	$indexed_dups = [];

	foreach($dups as $dup) {
		$compare_string = $dup['COMPARE_STRING'];
		$strip_id       = $dup['STRIP_ID'];

		if(!isset($indexed_dups[$compare_string])) {
			$indexed_dups[$compare_string] = [];
		}

		$indexed_dups[$compare_string][] = $strip_id;
	}

	$strip_hash_updates = [];
	$delete_ids         = [];

	$key = '_bk_remove_dup_strips_will';
	$tables = [
		'gl_journals',
		'gl_ap_request_allocation',
		'gl_ap_invoice_allocation',
		'gl_pos_receipt_allocation',
		'gl_manual_journal',
	];

	foreach($tables as $table_name) {
		if(Database::tableExists($table_name.$key)) {
			continue;
		}

		Database::query("SELECT * INTO {$table_name}{$key} FROM {$table_name}");

		foreach($indexed_dups as $compare_string => $dups) {
			$min_strip_id         = min($dups);
			$strip_hash_updates[] = $min_strip_id;
			$strip_in             = implode(', ', $dups);

			if(count($dups) === 1) {
				continue;
			}

			asort($dups);
			// Remove the first id with will be the valid strip id that we're keeping
			array_shift($dups);

			$delete_ids = array_merge($delete_ids, $dups);

			$sql = "
				UPDATE
					{$table_name}
				SET
					accounting_strip_id = {$min_strip_id}
				WHERE
					accounting_strip_id IN ({$strip_in})
			";
			Database::query($sql);
		}
	}

	if(!empty($delete_ids)) {
		$old_strip_ids = implode(', ', $delete_ids);
		// Remove the dup strips with the ids
		$sql = "
			DELETE FROM
				gl_accounting_strip
			WHERE
				id IN ($old_strip_ids)
		";
		Database::query($sql);
	}

	// Remove budgets that have a strip id that doesn't exist anymore
	$sql = "
		DELETE FROM
			gl_budget b
		WHERE
			NOT EXISTS (
				SELECT
					1
				FROM
					gl_accounting_strip s
				WHERE
					s.id = b.accounting_strip_id
			)
	";
	Database::query($sql);

	if(!empty($strip_hash_updates)) {
		$strip_in      = implode(', ', $strip_hash_updates);
		$update_strips = AccountingStrip::getAllAndLoad("id IN ({$strip_in})");

		foreach($update_strips as $strip) {
			$strip->persist();
		}
	}

	Database::query("DROP TABLE gl_tmp_dup_strips");

	// Create any budget records that are missing
	$sql = "
		SELECT
			DISTINCT accounting_strip_id
		FROM
			gl_journals j
		WHERE
			NOT EXISTS (
				SELECT
					1
				FROM
					gl_budget b
				WHERE
					b.accounting_strip_id = j.accounting_strip_id AND
					b.year = 2016
			) AND
			EXISTS (
				SELECT
					1
				FROM
					gl_accounting_strip s
				WHERE
					s.id = j.accounting_strip_id
			) AND
			(
				source ilike '%BM%' OR
				source ilike '%budget%'
			) AND
			journal_fiscal_year = 2016 AND
			accounting_strip_hash NOT LIKE '{\"-1\":\"-3\"%';
	";

	$tmp = Database::get($sql);
	foreach($tmp as $record) {
		$strip_id   = $record['ACCOUNTING_STRIP_ID'];
		$strip      = new AccountingStrip($strip_id);
		$strip_type = $strip->getCategoryType() === -1 ? 'E' : 'R';

		(new Budget())
			->setYear(2016)
			->setType($strip_type)
			->setAccountingStripId($strip_id)
			->persist();
	}
}

Database::commit();
