<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_dealer_insurance")) {
	$date = (Database::$type === "mssql") ? "DATETIME2" : "TIMESTAMP";
	$sql  =
		"CREATE TABLE gl_dealer_insurance (
			id BIGINT PRIMARY KEY,
			dealer_id BIGINT,
			title VARCHAR(255),
			account_number VARCHAR(255),
			expiration_date {$date}
		)";

	Database::query($sql);

	$sql =
		"UPDATE 
			gl_dealer_insurance 
		SET 
			id = {{next:gl_maint_seq}}";
	$sql = Database::preprocess($sql);

	Database::query($sql);

	$sql     =
		"SELECT
			id,
			insurance_expiration_date
		FROM
			gl_dealer
		WHERE
			insurance_expiration_date IS NOT NULL";
	$dealers = Database::get($sql);

	foreach ($dealers as $dealer) {
		(new DealerInsurance)
			->setDealerId($dealer["ID"])
			->setExpirationDate(date("Y-m-d H:i:s", strtotime($dealer["INSURANCE_EXPIRATION_DATE"])))
			->persist();
	}

	Database::dropColumn("gl_dealer", "insurance_expiration_date");
}

$sql  = 
	"SELECT
		(MAX(COALESCE(sort_order, 0)) + 1) AS new_sort
	FROM
		gl_contact_types";
$sort = Database::get($sql)[0]["NEW_SORT"];
$tmp  = ContactType::getOne([
	"title = 'P-Card E-mail'"
]);

if (!$tmp) {
	(new ContactType)
		->setTitle("P-Card E-mail")
		->setSortOrder($sort)
		->persist();
}

Database::commit();
return true;
?>