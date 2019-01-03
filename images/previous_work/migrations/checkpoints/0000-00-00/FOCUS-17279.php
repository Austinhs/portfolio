<?php
if (empty($GLOBALS["FocusFinanceConfig"]["enabled"])) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_fa_depreciation_log", "months")) {
	Database::createColumn("gl_fa_depreciation_log", "months", "INT");

	$sql        =
		"SELECT
			adl.id,
			adl.asset_id,
			adl.date,
			adl.amount,
			a.date_acquired,
			a.date_disposition,
			a.purchased_price,
			a.life
		FROM
			gl_fa_depreciation_log adl
		JOIN
			gl_fa_asset a
		ON
			a.id = adl.asset_id
		ORDER BY
			a.id ASC,
			adl.fiscal_year ASC";
	$res        = Database::reindex(Database::get($sql), ["ASSET_ID"]);
	$new_months = [];

	foreach ($res as $asset_id => $history) {
		$first_row       = array_shift($history);
		$last_row        = array_pop($history);
		$date_acquired   = $first_row["DATE_ACQUIRED"];
		$date_disposed   = $first_row["DATE_DISPOITION"];
		$purchased_price = $first_row["PURCHASED_PRICE"];
		$life            = $first_row["LIFE"];
		$yearly_amount   = $purchased_price / $life;

		// Handle first row
		if ($date_acquired) {
			$acquired_date_obj = new DateTime($date_acquired);
			$depreciation_date = $first_row["DATE"];

			if ($date_disposed && strtotime($date_disposed) < strtotime($depreciation_date)) {
				$depreciation_date = $date_disposed;
			}

			$depreciation_date_obj = new DateTime($depreciation_date);
			$difference            = $acquired_date_obj->diff($depreciation_date_obj);
			$years                 = $difference->y;
			$months                = ($years < 1) ? round($difference->format("%m")) : 12;

			if ($difference->format("%d") >= intval(Settings::get("fa_depreciation_day_threshold")) && $years < 1) {
				$months++;
			}

			$months = ($months > 0) ? $months : 1;

			if ($months !== 12) {
				$new_months[$first_row["ID"]] = $months;
			}
		}

		// Handle last row
		if (!$last_row || $last_row["ID"] === $first_row["ID"]) {
			continue;
		}

		$months = false;

		if ($date_disposed) {
			$date_disposed_obj     = new DateTime($date_disposed);
			$depreciation_date     = $last_row["DATE"];
			$depreciation_date_obj = new DateTime($depreciation_date);
			$difference            = $depreciation_date_obj->diff($date_disposed_obj);
			$months                = (int) $difference->format("%m");
		} else {
			$depreciation_amount   = $last_row["AMOUNT"];

			if ($depreciation_amount < $yearly_amount) {
				$months = round($depreciation_amount / $yearly_amount * 12);
			}
		}

		if ($months !== 12 && $months !== false) {
			$new_months[$last_row["ID"]] = $months;
		}
	}

	if ($new_months) {
		foreach ($new_months as $id => $months) {
			$params = [
				"id"     => $id,
				"months" => $months
			];
			$sql    =
				"UPDATE
					gl_fa_depreciation_log
				SET
					months = :months
				WHERE
					id = :id";

			Database::query($sql, $params);
		}
	}

	$sql =
		"UPDATE
			gl_fa_depreciation_log
		SET
			months = 12
		WHERE
			COALESCE(months, 0) = 0";

	Database::query($sql);
}

Database::commit();
return true;
?>