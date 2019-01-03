<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_fa_asset", "deleted")) {
	Database::createColumn("gl_fa_asset", "deleted", "INT");
}

Database::commit();

if ($GLOBALS["ClientId"] !== 7489238427) {
	return true;
}

Database::begin();

$subquery           = db_limit(
	"SELECT
		1
	FROM
		gl_fa_asset_allocation aa
	WHERE
		aa.asset_id = a.id AND
		COALESCE(aa.deleted, 0) = 0",
	1
);
$sql                =
	"SELECT DISTINCT
		a.id,
		COALESCE(r.id, l.request_id, 0) AS request_id
	FROM
		gl_fa_asset a
	LEFT JOIN
		gl_ap_request_line_item l
	ON
		l.id = a.line_item_id
	LEFT JOIN
		gl_ap_request r
	ON
		r.id = a.request_id
	WHERE
		COALESCE(a.deleted, 0) = 0 AND
		COALESCE(a.line_item_id, a.request_id, 0) != 0 AND
		NOT EXISTS
			(
				{$subquery}
			)";
$result             = Database::get($sql);
$ids                = array_column($result, "ID");
$ids                = implode(", ", $ids);
$requests           = Database::reindex($result, ["ID"]);
$assets             = FixedAsset::getAllAndLoad([
	"id IN ({$ids})"
]);
$insert_allocations = [];
$completed_assets   = [];

foreach ($assets as $asset_id => $asset) {
	if (isset($completed_assets[$asset_id])) {
		continue;
	}

	$category              = $asset->getCategory();
	$line_item_id          = $asset->getLineItemId();
	$request_id            = $requests[$asset_id][0]["REQUEST_ID"];
	$capitalized_object    = $category->getCapitalizedElementId();
	$noncapitalized_object = $category->getNoncapitalizedElementId();
	$track_non_capitalized = $category->getTrackNoncapitalized();
	$purchased_price       = $asset->getPurchasedPrice();
	$object_category_name  = ElementCategory::getObjectCategory()->getName();
	$line_join_sql         = ($line_item_id) ? "l.id = :line_item_id" : "l.price = :purchased_price";
	$sql                   =
		"SELECT DISTINCT
			a.*,
			l.qty,
			s.hash,
			s.{$object_category_name} AS object_id
		FROM
			gl_ap_request r
		JOIN
			gl_ap_request_line_item l
		ON
			l.request_id = r.id AND
			{$line_join_sql} AND
			COALESCE(l.deleted, 0) = 0 AND
			COALESCE(l.qty, 0) > 0
		JOIN
			gl_ap_request_allocation a
		ON
			a.request_id = r.id AND
			COALESCE(a.deleted, 0) = 0 AND
			(
				a.line_item_id = l.id OR
				a.reference_number = l.reference_number
			)
		JOIN
			gl_accounting_strip s
		ON
			s.id = a.accounting_strip_id AND
			COALESCE(s.{$object_category_name}, 0) != 0
		WHERE
			r.id = :request_id";
	$params                = [
		"request_id"            => (int) $request_id,
		"line_item_id"          => (int) $line_item_id,
		"purchased_price"     	=> $purchased_price,
		"capitalized_object"    => (int) $capitalized_object,
		"noncapitalized_object" => (int) $noncapitalized_object
	];
	$allocations          = Database::get($sql, $params);
	$total                = 0;
	$set_asset_request    = false;
	$unique_allocations   = [];
	$created_allocations  = false;

	foreach ($allocations as $allocation) {
		if ($allocation["OBJECT_ID"] === $noncapitalized_object && !$track_non_capitalized) {
			continue;
		}

		$allocation_id = $allocation["ID"];

		if (!isset($unique_allocations[$allocation_id])) {
			$unique_allocations[$allocation_id] = true;
		} else {
			continue;
		}

		if (!$set_asset_request) {
			$asset
				->setRequestId($allocation["REQUEST_ID"])
				->persist();

			$set_asset_request = true;
		}

		$total += $allocation["AMOUNT"];
	}

	$unique_allocations = [];

	foreach ($allocations as $allocation) {
		if ($allocation["OBJECT_ID"] === $noncapitalized_object && !$track_non_capitalized) {
			continue;
		}

		$allocation_id = $allocation["ID"];

		if (!isset($unique_allocations[$allocation_id])) {
			$unique_allocations[$allocation_id] = true;
		} else {
			continue;
		}

		$percent              = $allocation["AMOUNT"] / $total;
		$amount               = $purchased_price * $percent;
		$percent             *= 100.00;
		$insert_allocations[] = (new FAAssetAllocation)
			->setAssetId($asset->getId())
			->setAmount($amount)
			->setPercent($percent)
			->setStartDate($asset->getDateAcquired())
			->setAccountingStripId($allocation["ACCOUNTING_STRIP_ID"])
			->setAccountingStripHash($allocation["HASH"])
			->setRequestId($allocation["REQUEST_ID"]);
		$created_allocations  = true;
	}

	if ($created_allocations) {
		$completed_assets[$asset_id] = true;
	}
}

if ($insert_allocations) {
	DatabaseObject::insert($insert_allocations);
}

Database::commit();
return true;
?>