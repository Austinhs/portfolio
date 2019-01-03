<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if ($GLOBALS["ClientId"] === 12345678) {
	Database::begin();

	$sql =
		"WITH depreciation_data AS (
			SELECT 
				ROW_NUMBER() OVER(PARTITION BY asset_id ORDER BY fiscal_year DESC) AS row_number, 
				asset_id, 
				fiscal_year, 
				SUM(amount) AS amount 
			FROM 
				gl_fa_depreciation_log 
			GROUP BY 
				asset_id, 
				fiscal_year
		)
		SELECT
			a.id,
			COALESCE(dc.amount, 0) AS current_depreciated,
			SUM(COALESCE(da.amount, 0)) AS accumulated_depreciation,
			SUM(COALESCE(da.amount, 0)) + COALESCE(dc.amount, 0) AS total_depreciation
		FROM
			gl_fa_asset a 
		LEFT JOIN
			depreciation_data dc
		ON
			dc.asset_id = a.id AND
			dc.row_number = 1
		LEFT JOIN
			depreciation_data da
		ON
			da.asset_id = a.id AND
			da.row_number > 1
		GROUP BY
			a.id,
			a.purchased_price,
			a.salvage_value,
			a.accumulated_depreciation,
			dc.amount
		HAVING
			(
				CAST(SUM(COALESCE(da.amount, 0)) + COALESCE(dc.amount, 0) AS NUMERIC) = CAST(COALESCE(a.purchased_price, 0) + COALESCE(a.salvage_value, 0) AS NUMERIC) AND
				CAST(SUM(COALESCE(da.amount, 0)) + COALESCE(dc.amount, 0) AS NUMERIC) != CAST(COALESCE(a.accumulated_depreciation, 0) AS NUMERIC)
			) OR
			(
				CAST(SUM(COALESCE(da.amount, 0)) + COALESCE(dc.amount, 0) AS NUMERIC) != CAST(COALESCE(a.purchased_price, 0) + COALESCE(a.salvage_value, 0) AS NUMERIC) AND
				CAST(SUM(COALESCE(da.amount, 0)) AS NUMERIC) != CAST(COALESCE(a.accumulated_depreciation, 0) AS NUMERIC)
			)";
	$res = Database::get($sql);

	foreach ($res as $data) {
		$asset                          = new FixedAsset($data["ID"]);
		$salvage_value                  = $asset->getSalvageValue();
		$purchased_price                = $asset->getPurchasedPrice();
		$asset_accumulated_depreciation = $asset->getAccumulatedDepreciation();
		$asset_current_depreciated      = $asset->getCurrentDepreciatedValue();
		$accumulated_depreciation       = $data["ACCUMULATED_DEPRECIATION"];
		$current_depreciated            = $data["CURRENT_DEPRECIATED"];
		$total_depreciation             = $data["TOTAL_DEPRECIATION"];

		if ($total_depreciation >= ($purchased_price + $salvage_value)) {
			$asset
				->setAccumulatedDepreciation($total_depreciation)
				->setCurrentDepreciatedValue(0)
				->persist();
		} else if ($total_depreciation < ($purchased_price + $salvage_value)) {
			$asset
				->setAccumulatedDepreciation($accumulated_depreciation)
				->setCurrentDepreciatedValue($current_depreciated)
				->persist();
		} else if (!$total_depreciation) {
			$asset
				->setAccumulatedDepreciation(0)
				->setCurrentDepreciatedValue(0)
				->persist();
		}
	}

	(new Setting)
		->setKey("fa_depreciate_with_allocation_only")
		->setValue(1)
		->persist();

	Database::commit();
}

return true;
?>