<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

Database::begin();

if(!Database::columnExists('gl_journals', 'accounting_strip_hash')) {
	Database::createColumn('gl_journals', 'accounting_strip_hash', 'text');
}

if(!Database::columnExists('gl_initial_account_balance', 'accounting_strip_hash')) {
	Database::createColumn('gl_initial_account_balance', 'accounting_strip_hash', 'text');
}

$sql = "
	UPDATE
		gl_journals
	SET
		accounting_strip_hash = hash
	FROM
		gl_accounting_strip
	WHERE
		gl_journals.accounting_strip_id = gl_accounting_strip.id AND
		COALESCE(gl_journals.accounting_strip_id, 0) != 0
";
Database::query($sql);

$fund_category_id = ElementCategory::getFundCategoryId();

$sql = "
	SELECT
		id
	FROM
		gl_element
	WHERE
		COALESCE(deleted, 0) = 0 AND
		element_category_id = :category_id
";

$params = [
	'category_id' => intval($fund_category_id)
];

$elements = Database::get($sql, $params);

foreach($elements as $element) {
	$element_id = intval($element['ID']);
	$hash       = json_encode([(String) $fund_category_id => (String) $element_id]);

	$sql = "
		UPDATE
			gl_journals
		SET
			accounting_strip_hash = :hash
		WHERE
			COALESCE(accounting_strip_id, 0) = 0 AND
			fund = :element_id
	";

	$params = [
		'hash'       => $hash,
		'element_id' => $element_id,
	];

	Database::query($sql, $params);
}

$sql = "
	UPDATE
		gl_journals
	SET
		source = 'AP Void Invoice Unexpended'
	WHERE
		source = 'AP Void Invoice' AND
		COALESCE(debit_account_id, 0) != 0 AND
		COALESCE(credit_account_id, 0) != 0
";
Database::query($sql);

Database::commit();
