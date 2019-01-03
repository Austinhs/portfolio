<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$vendors = Vendor::getAllAndLoad();

foreach ($vendors as $vendor_id => $vendor) {
	$hashes    = [];
	$addresses = Address::getAllAndLoad(
		[
			"parent_id = :vendor_id",
			"parent_class = 'Dealer'"
		],
		null,
		[
			"vendor_id" => $vendor_id
		]
	);

	foreach ($addresses as $address) {
		$record = $address->getRecord();

		unset($record["ID"]);
		ksort($record);

		$hash = hash("sha256", serialize($record));

		if (in_array($hash, $hashes)) {
			$address->delete();
			continue;
		}

		$hashes[] = $hash;
	}
}

Database::commit();
return true;
?>