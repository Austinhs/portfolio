<?php
if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

$where = "type = '13'";
$test  = WarehouseTransaction::getAllAndLoad($where);

if(empty($test)) {
	Database::begin();
	$facility_category = ElementCategory::getCostCenterCategory();
	$facility_name     = $facility_category->getName();

	if(!Database::columnExists('gl_wh_types', 'internal')){
		Database::createColumn('gl_wh_types', 'internal', 'bigint');
	}

	if(!Database::columnExists('gl_wh_pools', 'pos_active')){
		Database::createColumn('gl_wh_pools', 'pos_active', 'bigint');
	}

	if(!Database::columnExists('gl_wh_items', 'taxable')){
		Database::createColumn('gl_wh_items', 'taxable', 'bigint');
	}

	if(!Database::columnExists('gl_wh_items', 'service')){
		Database::createColumn('gl_wh_items', 'service', 'bigint');
	}

	if(!Database::columnExists('gl_wh_items', 'internal')){
		Database::createColumn('gl_wh_items', 'internal', 'bigint');
	}

	if(!Database::columnExists('gl_wh_items', 'accounting_strip_id')){
		Database::createColumn('gl_wh_items', 'accounting_strip_id', 'bigint');
	}

	if(!Database::columnExists('gl_wh_items', 'debit_account_id')){
		Database::createColumn('gl_wh_items', 'debit_account_id', 'bigint');
	}

	if(!Database::columnExists('gl_wh_items', 'credit_account_Id')){
		Database::createColumn('gl_wh_items', 'credit_account_Id', 'bigint');
	}

	if(!Database::columnExists('gl_wh_items', 'ten_ninety_eight')){
		Database::createColumn('gl_wh_items', 'ten_ninety_eight', 'bigint');
	}

	if(!Database::columnExists('gl_wh_items', 'sales_price')){
		Database::createColumn('gl_wh_items', 'sales_price', 'numeric(28, 10)');
	}

	$sql = "
		SELECT
			pr.id as product_id,
			CONCAT(pr.name, ' ', pr.description) as description,
			pr.debit_account_id,
			pr.amount as sales_price,
			pr.barcode as item_number,
			pr.credit_account_id,
			COALESCE(pr.internal, 0) as internal,
			pr.active,
			pr.accounting_strip_id,
			CONCAT(pr.accounting_strip_id,pr.debit_account_id,pr.credit_account_id) as type_key,
			pr.tax as taxable,
			pr.service,
			pr.quantity,
			pr.ten_ninety_eight,
			facility.id as facility_id
		FROM
			gl_ar_product pr LEFT JOIN
			gl_accounting_strip strip ON strip.id = pr.accounting_strip_id LEFT JOIN
			gl_facilities facility on facility.facility_element_id = strip.{$facility_name}
		WHERE
			pr.deleted IS NULL
	";

	$products = Database::get($sql);
	$types    = array();
	$pools    = array();

	$imported_type = (new WarehouseType)
		->setName('Default Sales Type')
		->persist();

	$internal_type = (new WarehouseType)
		->setName('Default Internal Sales Type')
		->setInternal(1)
		->persist();

	$sales_types = array(
		0 => $imported_type->getId(),
		1 => $internal_type->getId()
	);

	foreach($products as $product) {
		$product = array_change_key_case($product, CASE_LOWER);
		extract($product);
		$internal = intval($internal);

		if(empty($facility_id)) {
			$district    = Facility::getDistrictFacility();
			$facility_id = $district->getId();
		}

		if(!isset($pools[$facility_id])) {
			$pool     = new WarehousePool();
			$facility = new Facility($facility_id);

			$facility
				->setWarehouse(1)
				->persist();

			$pool
				->setName($facility->getName())
				->setFacilityId($facility_id)
				->setPosActive(1)
				->persist();

			$pools[$facility_id] = $pool;
		} else {
			$pool = $pools[$facility_id];
		}

		$item_number = str_replace("'", "''", $item_number);

		$item = new ARWarehouseItem();
		$item
			->setTaxable($taxable)
			->setService($service)
			->setDescription($description)
			->setTypeId($sales_types[$internal])
			->setPoolId($pool->getId())
			->setInternal($internal)
			->setAccountingStripId($accounting_strip_id)
			->setDebitAccountId($debit_account_id)
			->setCreditAccountId($credit_account_id)
			->setSalesPrice($sales_price)
			->setItemNumber($item_number)
			->setStockItem($active)
			->setTenNinetyEight($ten_ninety_eight)
			->persist();

		$item_id = $item->getId();

		$transaction = new WarehouseTransaction();
		$transaction
			->setSource('Product')
			->setSourceId($product_id)
			->setType(13)
			->setItemId($item_id)
			->setDate(DBDate())
			->setPrice(floatval($sales_price) * floatval($quantity))
			->setCreatedBy(ERPUser::getCurrent()->getId())
			->setAllocated($quantity)
			->persist();

		$tables = array(
			"gl_pos_invoice_allocation",
			"gl_pos_receipt_line",
			"gl_pos_receipt_allocation"
		);

		foreach ($tables as $table) {
			Database::query("
				UPDATE
					{$table}
				SET
					product_id = {$item_id}
				WHERE
					product_id = {$product_id}
			");
		}
	}

	Database::commit();
}
?>
