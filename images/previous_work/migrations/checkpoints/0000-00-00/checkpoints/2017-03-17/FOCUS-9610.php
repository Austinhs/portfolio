<?php
if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

if (Database::tableExists("gl_element_range") && !Database::columnExists("gl_element_range", "id")) {
	Database::begin();
	Database::createColumn("gl_element_range", "id", "BIGINT", "", false);

	$sql =
		"ALTER TABLE
			gl_element_range
		ADD PRIMARY KEY
			(id)";
	Database::query($sql);

	$sql = 
		"UPDATE 
			gl_element_range 
		SET 
			id = {{next:gl_maint_seq}}";
	$sql = Database::preprocess($sql);
	Database::query($sql);
	Database::commit();
}

/**
 * Step 1: Add permissions to the new "Element Ranges" settings tab if they have permissions to "Elements"
 */
$postgres = Database::$type === 'postgres';
$key      = "setting::setup-elements";
$newKey   = "setting::setup-element-ranges";
$existing = [];
$results  = Database::get(
	"SELECT
		DISTINCT profile_id
	FROM
		" . Permission::$table . "
	WHERE
		\"key\" = '{$key}'"
);
$results2 = Database::get(
	"SELECT
		DISTINCT profile_id
	FROM
		" . Permission::$table . "
	WHERE
		\"key\" = '{$newKey}'"
);

foreach ($results2 as $result) {
	$profileId            = $result["PROFILE_ID"];
	$existing[$profileId] = $profileId;
}

Database::begin();

foreach ($results as $result) {
	$profileId = $result["PROFILE_ID"];

	if (isset($existing[$profileId])) {
		continue;
	}

 	(new Permission())
		->setProfileId($profileId)
		->setKey($newKey)
		->persist();
}

Database::commit();

$postgres_primary_key = "";
$mssql_primary_key    = "";
if($postgres) {
	$postgres_primary_key = ", PRIMARY KEY (id)";
}
else {
	$mssql_primary_key = "NOT NULL PRIMARY KEY";
}

/**
 * Step 2: Create the gl_element_range table if it wasn't added from metadata
 */
if (!Database::tableExists("gl_element_range")) {
	$sql =
		"CREATE TABLE gl_element_range (
			id BIGINT {$mssql_primary_key},
			deleted BIGINT,
			element_category_id BIGINT,

			code VARCHAR(255),
			title VARCHAR(255)

			{$postgres_primary_key}
		)";

	Database::begin();
	Database::query($sql);
	Database::commit();
}

/**
 * Step 3: Check if any (even deleted) element ranges exist
 */
$sql = db_limit(
	"SELECT
		1
	FROM
		gl_element_range",
	1
);

$results = Database::get($sql);

if (count($results)) {
	return;
}

/**
 * Step 4: If no element ranges exist, populate the table with Red Book ranges
 */
$skipRevenue  = false;
$skipFund     = false;
$skipFunction = false;
$skipObject   = false;

// Have to put each category ID fetch into a try/catch otherwise the entire migration will fail if any one category is not set up
try {
	$revenueCategoryId = ElementCategory::getRevenueCategoryId();
} catch (Exception $e) {
	$skipRevenue = true;
}

try {
	$fundCategoryId = ElementCategory::getFundCategoryId();
} catch (Exception $e) {
	$skipFund = true;
}

try {
	$functionCategoryId = ElementCategory::getFunctionCategoryId();
} catch (Exception $e) {
	$skipFunction = true;
}

try {
	$objectCategoryId = ElementCategory::getObjectCategoryId();
} catch (Exception $e) {
	$skipObject = true;
}

$topLevelRevenue = [
	"3100" => "Federal Direct",
	"3200" => "Federal Through State and Local",
	"3300" => "Revenues From State Sources",
	"3400" => "Revenues From Local Sources",
	"3600" => "Transfers",
	"3700" => "Face Value of Long-term Debt and Sale of Capital Assets"
];

$topLevelFunds = [
	"000" => "Permanent Funds",
	"100" => "General Fund",
	"200" => "Debt Service Funds",
	"300" => "Capital Projects Funds",
	"400" => "Special Revenue Funds",
	"700" => "Internal Service Funds",
	"800" => "Fiduciary Funds",
	"900" => "Enterprise Funds"
];

$topLevelObjects = [
	"100" => "Salaries",
	"200" => "Employee Benefits",
	"300" => "Purchased Services",
	"400" => "Energy Services",
	"500" => "Materials and Supplies",
	"600" => "Capital Outlay",
	"700" => "Other",
	"800" => "Other",
	"900" => "Transfers"
];

$topLevelFunctions = [
	"5000" => "Instruction",
	"6000" => "Student and Instructional Support Services",
	"7000" => "General Support Services",
	"8000" => "General Support Services",
	"9100" => "Community Services",
	"9200" => "Debt Service",
	"9300" => "Other Capital Outlay",
	"9700" => "Transfers",
	"9900" => "Proprietary and Fudiciary Expenses"
];

Database::begin();

$columns = [
	"title",
	"code",
	"element_category_id"
];

$values = [];

if (!$skipRevenue) {
	foreach ($topLevelRevenue as $code => $title) {
		$values[] = [
			"title"               => $title,
			"code"                => $code,
			"element_category_id" => $revenueCategoryId
		];
	}
}

if (!$skipFund) {
	foreach ($topLevelFunds as $code => $title) {
		$values[] = [
			"title"               => $title,
			"code"                => $code,
			"element_category_id" => $fundCategoryId
		];
	}
}

if (!$skipFunction) {
	foreach ($topLevelFunctions as $code => $title) {
		$values[] = [
			"title"               => $title,
			"code"                => $code,
			"element_category_id" => $functionCategoryId
		];
	}
}

if (!$skipObject) {
	foreach ($topLevelObjects as $code => $title) {
		$values[] = [
			"title"               => $title,
			"code"                => $code,
			"element_category_id" => $objectCategoryId
		];
	}
}

// Using Database insert because sometimes this migration thinks ElementRange is not a class (upon initial merge)
Database::insert("gl_element_range", "gl_maint_seq", $columns, $values);

Database::commit();
?>
