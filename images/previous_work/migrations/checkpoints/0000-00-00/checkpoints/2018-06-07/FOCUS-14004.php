<?php

if (empty($GLOBALS['FocusFinanceConfig']['enabled'])) {
	return false;
}


$already_run = Database::get("SELECT ID FROM CUSTOM_FIELDS WHERE ALIAS = 'communications_log'");

if (empty($already_run)) {
	Database::begin();

	//Add Categories
	$comm_log_cat = new CustomFieldCategory();
	$comm_log_cat->setTitle('Communications Log');
	$comm_log_cat->setErp(1);
	$comm_log_cat->setSourceClass('FocusUser');
	$comm_log_cat->setSortOrder(8675309);
	$comm_log_cat->persist();
	$comm_log_cat->fixSortOrders();

	$comm_log_cat_id = $comm_log_cat->getId();

	$cf = new CustomField();
	$cf->setTitle('Communications Log');
	$cf->setAlias('communications_log');
	$cf->setType('log');
	$cf->setSourceClass('FocusUser');
	$cf->setSystem(1);
	$cf->persist();

	$field_id = $cf->getId();

	$cfjc = new CustomFieldJoinCategory();
	$cfjc->setCategoryId($comm_log_cat_id);
	$cfjc->setFieldId($field_id);
	$cfjc->setSortOrder(8675309);
	$cfjc->persist();
	$cfjc->fixSortOrders();

	$log_columns = [
		[
			'title' => 'Date',
			'type' => 'date',
			'col_name' => 'date',
			'skip' => false
		],
		[
			'title' => 'Contact Made By',
			'type' => 'text',
			'col_name' => 'contact_made_by',
			'skip' => false
		],
		[
			'title' => 'Notes',
			'type' => 'textarea',
			'col_name' => 'notes',
			'skip' => false
		],
		[
			'title' => 'Files',
			'type' => 'file',
			'col_name' => 'date',
			'skip' => true
		]
	];

	$log_cols = array();
	foreach ($log_columns as $log_column) {
		$lc = new CustomFieldLogColumn();
		$lc->setFieldId($field_id);
		$lc->setTitle($log_column['title']);
		$lc->setType($log_column['type']);
		$lc->persist();
		if (!$log_column['skip']) {
			$log_cols[$log_column['col_name']] = $lc->getColumnName();
		}
	}

	$sql = Database::preprocess(
		"INSERT INTO CUSTOM_FIELD_LOG_ENTRIES
			(id, SOURCE_CLASS, FIELD_ID, SOURCE_ID, ".implode(',', $log_cols).")
		SELECT
			{{next:custom_field_log_entries_seq}} as Id,
			'FocusUser' as SOURCE_CLASS,
			{$field_id} as FIELD_ID,
			STAFF_ID as SOURCE_ID,
			".implode(',', array_keys($log_cols))."
		FROM GL_HR_COMMUNICATIONS_LOG"
	);

	Database::query($sql);
	Database::query("DROP TABLE GL_HR_COMMUNICATIONS_LOG");
	Database::commit();
}
