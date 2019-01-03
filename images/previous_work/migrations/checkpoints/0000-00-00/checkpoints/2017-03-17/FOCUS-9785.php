<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

Database::begin();

if(!Database::columnExists('gl_ap_request_line_item', 'asset_category_id')) {
	Database::createColumn('gl_ap_request_line_item', 'asset_category_id', 'bigint');
}

if(!Database::columnExists('gl_ap_request_line_item', 'asset_object_id')) {
	Database::createColumn('gl_ap_request_line_item', 'asset_object_id', 'bigint');
}

Database::commit();

if(!Settings::get('object_category'))
	return true;
$objectCategory = ElementCategory::getObjectCategory()->getName();
$sql            =
	"SELECT
		rl.id,
		fa.category_id,
		s.{$objectCategory} AS object_id
	FROM
		gl_ap_request_line_item rl
	JOIN
		gl_fa_asset fa
	ON
		fa.line_item_id = rl.id
	LEFT JOIN
		gl_ap_request_allocation ra
	ON
		ra.request_id = rl.request_id AND
		ra.reference_number = rl.reference_number
	LEFT JOIN
		gl_accounting_strip s
	ON
		s.id = ra.accounting_strip_id
	WHERE
		COALESCE(rl.asset_category_id, 0) = 0 AND
		COALESCE(rl.asset_object_id, 0) = 0 AND
		COALESCE(ra.deleted, 0) = 0 AND
		COALESCE(fa.category_id, 0) != 0";

$results = Database::get($sql);

Database::begin();

foreach ($results as $result) {
	$id         = $result["ID"];
	$categoryId = $result["CATEGORY_ID"];
	$objectId   = $result["OBJECT_ID"];

	(new RequestLineItem($id))
		->setAssetCategoryId($categoryId)
		->setAssetObjectId($objectId)
		->persist();
}

Database::commit();
?>
