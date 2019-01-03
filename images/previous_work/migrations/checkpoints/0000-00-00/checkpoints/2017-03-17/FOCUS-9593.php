<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

/**
 * Helper class
 */
class Migration9593 {
	/**
	 * Loop the recons to see which ones don't have matching balances
	 * @param   $recons    Array    Array of BankReconciliation DatabaseObjects
	 * @param   $quickRun  Boolean  Whether we want to break the data down into debits/credits or just return ID's of bad recons
	 * @return             Array    Array of recon data depending on $quickRun
	 */
	public static function buildBadRecons($recons, $banks, $quickRun = false) {
		$badRecons = [];

		foreach ($recons as $recon) {
			if ($recon->getDeleted()) {
				continue;
			}

			$bank_id = intval($recon->getBankId());

			if(empty($banks[$bank_id])) {
				continue;
			}

			$bank     = $banks[$bank_id];
			$internal = intval(intval($bank->getFundId()) === intval(Settings::get("internal_account_fund")));
			$recon->process($internal);

			$balances = $recon->getBalances();
			$reconId  = $recon->getId();

			// Recon is good to go
			if (trim($balances["system"]["ending"]) === trim($balances["bank"]["ending"])) {
				if ($balances["system"]["ending"] !== $recon->getEndingBalance()) {
					$recon
						->setEndingBalance($balances["system"]["ending"])
						->persist();

					$bank
						->setLastReconciledAmount($balances["system"]["ending"])
						->persist();
				}

				continue;
			}

			// Just doing a quick run to get the ID's of bad recons
			if ($quickRun) {
				$badRecons[$reconId] = $reconId;
				continue;
			}

			// Array of relevant recon data
			$reconData = [
				"recon"   => $recon,
				"credits" => true,
				"debits"  => true
			];

			// There is a mismatch of the credits
			if (trim($balances["system"]["credits"]) !== trim($balances["bank"]["credits"])) {
				$reconData["credits"] = false;
			}

			// There is a mismatch of the debits
			if (trim($balances["system"]["debits"]) !== trim($balances["bank"]["debits"])) {
				$reconData["debits"] = false;
			}

			$badRecons[$reconId] = $reconData;
		}

		return $badRecons;
	}

	/**
	 * Loop the recons to see if the credits/debits have been corrected
	 * @param   $badRecons  Array   Array produced by self::buildBadRecons()
	 * @param   $type       String  "debits" or "credits" - the field to check against
	 * @return              Void
	 */
	public static function updateBadRecons(&$badRecons, $type) {
		foreach ($badRecons as &$recon) {
			// Recon is fine, skip it
			if ($recon[$type]) {
				continue;
			}

			$balances     = $recon["recon"]->getBalances();
			$recon[$type] = true;

			// Debits still don't match
			if (trim($balances["system"][$type]) !== trim($balances["bank"][$type])) {
				$recon[$type] = false;
			}
		}
	}
}

Database::begin();

if (!Database::columnExists("gl_banks", "last_reconciled_amount")) {
	Database::createColumn("gl_banks", "last_reconciled_amount", "numeric");
}

if (!Database::columnExists("gl_ba_checks", "cleared_by_import")) {
	Database::createColumn("gl_ba_checks", "cleared_by_import", "BIGINT");
}

/**
 * Soft delete all errors that are linked to recon data entries that no longer exist for some reason
 */
$sql =
	"UPDATE
		gl_ba_bank_reconciliation_error
	SET
		deleted = 1
	WHERE
		NOT EXISTS
			(
				SELECT
					1
				FROM
					gl_ba_bank_reconciliation_data d
				WHERE
					d.id = reconciliation_data_id
			)";

Database::query($sql);

/**
 * Ensure all checks don't have a cleared_date or reconciliation_id if they don't exist in the recon data table
 */
$sql = 
	"UPDATE 
		gl_ba_checks
	SET 
		cleared_date = NULL,
		reconciliation_id = NULL
	WHERE
		NOT EXISTS 
			(
				SELECT 
					1
				FROM 
					gl_ba_bank_reconciliation_data d
				WHERE
					d.source_id = gl_ba_checks.id AND
					COALESCE(d.deleted, 0) = 0
			) AND
		(cleared_date IS NOT NULL OR COALESCE(reconciliation_id, 0) != 0) AND 
		COALESCE(cleared_by_import, 0) = 0";

Database::query($sql);

/**
 * Assign all checks a cleared date and recon ID if they are in the recon data table but don't have a cleared date or recon ID
 */
// Preload all the recon data
$reconData = BankReconciliationData::getAllAndLoad([
	"COALESCE(deleted, 0) = 0",
	"COALESCE(check_number, 0) != 0"
]);

// Get checks that are in the recon data table but don't have cleared dates and/or recon IDs
// Have to select the r.deleted value rather than adding it to ON or WHERE because apparently that makes this query take literally forever...
$sql =
	"SELECT
		c.id AS check_id,
		d.id AS data_id,
		r.deleted
	FROM
		gl_ba_checks c
	JOIN
		gl_ba_bank_reconciliation_data d
	ON
		CAST(d.check_number AS VARCHAR) = c.check_number AND
		COALESCE(d.deleted, 0) = 0
	JOIN
		gl_ba_bank_reconciliation r
	ON
		r.id = d.reconciliation_id AND
		r.bank_id = c.bank_id
	WHERE
		COALESCE(c.reconciliation_id, -1) = -1 OR
		c.cleared_date IS NULL";

$results = Database::get($sql);

// Preload all the necessary checks
$checkIds = [];

foreach ($results as $result) {
	$checkIds[] = $result["CHECK_ID"];
}

$checkIdsString = implode(", ", $checkIds);

if ($checkIds) {
	$checks = Check::getAllAndLoad([
		"id IN ({$checkIdsString})"
	]);
}

// Assign cleared dates and recon IDs to the resulting checks
foreach ($results as $result) {
	if ($result["DELETED"]) {
		continue;
	}

	$check = $checks[$result["CHECK_ID"]];
	$data  = $reconData[$result["DATA_ID"]];

	if (!$check->getClearedDate()) {
		$check->setClearedDate($data->getTransactionDate());
	}

	if (!$check->getReconciliationId()) {
		$check->setReconciliationId($data->getReconciliationId());
	}

	$check->persist();
}

/**
 * Correct recons that don't have matching balances
 */
// Get all the finalized recons
$recons = BankReconciliation::getAllAndLoad([
	"COALESCE(finalized, 0) = 1",
	"finalized_date IS NOT NULL"
]);

$banks  = Bank::getAllAndLoad();

// Loop the recons to see which ones don't have matching balances
$badRecons = Migration9593::buildBadRecons($recons, $banks);

// If all the recons balance, exit this migration to avoid needlessly looping these recons over and over to check the balances
if (!$badRecons) {
	Database::commit();
	return true;
}

// Build a comma-delimited ID string of the recons with mismatched credits
$reconsString = implode(", ",
	array_keys(
		array_filter($badRecons, function($recon) {
			return (!$recon["credits"]);
		})
	)
);

// Update all unreconciled credits on old recons otherwise they will show as $0 in the new recon system
if ($reconsString) {
	$sql =
		"UPDATE
			gl_ba_bank_reconciliation_data
		SET
			source_class = 'FOCUS',
			source_id = 1
		WHERE
			source_class IS NULL AND
			credit_amount IS NOT NULL AND
			reconciliation_id IN ({$reconsString})";

	Database::query($sql);
}

// Build a comma-delimited ID string of the recons with mismatched debits
$reconsString = implode(", ",
	array_keys(
		array_filter($badRecons, function($recon) {
			return (!$recon["debits"]);
		})
	)
);

// Update all unreconciled debits on old recons otherwise they will show as $0 in the new recon system
if ($reconsString) {
	$sql =
		"UPDATE
			gl_ba_bank_reconciliation_data
		SET
			source_class = 'FOCUS',
			source_id = 1
		WHERE
			source_class IS NULL AND
			debit_amount IS NOT NULL AND
			reconciliation_id IN ({$reconsString}) AND
			COALESCE(exclude_from_balances, 0) = 0";

	Database::query($sql);
}

// Loop the recons again to see if the debits have been corrected
Migration9593::updateBadRecons($badRecons, "debits");

// Build a comma-delimited ID string of the recons with mismatched debits
$reconsString = implode(", ",
	array_keys(
		array_filter($badRecons, function($recon) {
			return (!$recon["debits"]);
		})
	)
);

// For recons with mismatched debits, try converting some "unreconciled" checks to debits
if ($reconsString) {
	$sql =
		"UPDATE
			gl_ba_bank_reconciliation_data
		SET
			check_number = -1 * check_number
		WHERE
			reconciliation_id IN ({$reconsString}) AND
			source_class = 'FOCUS' AND
			check_number IS NOT NULL AND
			check_number > 0";

	Database::query($sql);
}

// Loop the recons again to see if the debits have been corrected
Migration9593::updateBadRecons($badRecons, "debits");

// Build a comma-delimited ID string of the recons with mismatched debits
$reconsString = implode(", ",
	array_keys(
		array_filter($badRecons, function($recon) {
			return (!$recon["debits"]);
		})
	)
);

// For recons with mismatched debits, soft delete the "not in system" reconciled check errors since their corresponding data records
// would have been caught and updated to debits in the previous query
if ($reconsString) {
	$sql =
		"UPDATE
			gl_ba_bank_reconciliation_error
		SET
			deleted = 1
		WHERE
			reconciliation_id IN ({$reconsString}) AND
			COALESCE(deleted, 0) = 0 AND
			COALESCE(reconciled, 0) = 1 AND
			message = 'ERROR_CHECK_NOT_IN_SYSTEM'";

	Database::query($sql);
}

Database::commit();

/**
 * We're done, so cross our fingers and hope all the recons now match - if they don't, they will require manual correction
 * Send an email if there are any leftover recons that don't balance so Patrick can correct them
 */
$sendEmail = true;

if (!$sendEmail) {
	return true;
}

// Loop the recons to see which ones don't have matching balances
$badRecons = Migration9593::buildBadRecons($recons, $banks, true);

if ($badRecons) {
	// Construct the email data
	global $FocusURL;
	$reconCount   = count($badRecons);
	$reconsString = implode(", ", $badRecons);
	$message      = "Site: {$FocusURL}<br><br>" .
					"Total errant recons: {$reconCount}<br><br>" .
					"Recon ID's: {$reconsString}";
	$subject      = "Errant bank recon data found on {$FocusURL}";
	$emailAddress = "patrickg@focusschoolsoftware.com";

	// Send the email
	Email::send($subject, $message, $emailAddress);
}

return true;
?>