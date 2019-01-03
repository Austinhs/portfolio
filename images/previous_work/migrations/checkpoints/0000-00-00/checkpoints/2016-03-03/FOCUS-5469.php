<?php

Migrations::depend('FOCUS-6359');

$text_type = Database::$type === 'mssql' ? 'VARCHAR(MAX)' : 'TEXT';

// Edit Rules/Linked Fields
if(!Database::tableExists('edit_rules')) {
	Database::query("
		CREATE TABLE edit_rules (
			id BIGINT PRIMARY KEY,
			name VARCHAR(255) NOT NULL,
			enabled BIGINT NULL,
			message {$text_type} NULL,
			prevents_saving INTEGER NULL,
			category VARCHAR(255) NOT NULL,
			system INTEGER NULL,
			deleted BIGINT NULL
		)
	");
}

if(!Database::sequenceExists('edit_rules_seq')) {
	Database::createSequence('edit_rules_seq');
}

if(!Database::tableExists('edit_rule_criteria')) {
	Database::query("
		CREATE TABLE edit_rule_criteria (
			id BIGINT PRIMARY KEY,
			deleted BIGINT NULL,
			field1 VARCHAR(255) NOT NULL,
			field2 VARCHAR(255) NULL,
			type VARCHAR(255) NULL,
			value {$text_type} NULL,
			rule_id BIGINT NOT NULL,
			reversed INTEGER NULL,
			not_empty INTEGER NULL
		)
	");
}

if(!Database::sequenceExists('edit_rule_criteria_seq')) {
	Database::createSequence('edit_rule_criteria_seq');
}

if(!Database::tableExists('linked_fields')) {
	Database::query("
		CREATE TABLE linked_fields (
			id BIGINT PRIMARY KEY,
			rule_id BIGINT NOT NULL,
			linked_field VARCHAR(255) NOT NULL,
			value {$text_type} NULL,
			type VARCHAR(255) NULL,
			deleted INTEGER NULL
		)
	");
}

if(!Database::sequenceExists('linked_fields_seq')) {
	Database::createSequence('linked_fields_seq');
}

// Other migrations from wherever
if(!empty($GLOBALS['FocusFinanceConfig']['installed'])) {
	$type_category = ElementCategory::getOneAndLoad('id = -1');
	$date          = date('Y-m-d H:i:s');

	if(!$type_category) {
		// Add the "Type" element category along with its elements
		$type_category = (new ElementCategory())
			->setTitle("Type")
			->setBudgeted(1)
			->persist();

		Database::query("UPDATE gl_element_category SET id = -1 WHERE id = " . $type_category->getId());

		$type_elements = [
			-1 => ["title" => "Expense", "code" => "E"],
			-2 => ["title" => "Revenue", "code" => "R"],
			-3 => ["title" => "Internal Accounts", "code" => "I"],
		];

		foreach($type_elements as $id => $definition) {
			$element = (new Element())
				->setElementCategoryId(-1)
				->setTitle($definition["title"])
				->setCode($definition["code"])
				->persist();

			Database::query("UPDATE gl_element SET id = {$id} WHERE id = " . $element->getId());
		}
	}

	// Update existing strips
	Database::query("UPDATE gl_accounting_strip SET updated_at = '{$date}', category_type = -1, hash = CONCAT('{\"-1\":\"-1\",', SUBSTRING(hash, 2, 255)) WHERE type = 'E' AND category_type IS NULL");
	Database::query("UPDATE gl_accounting_strip SET updated_at = '{$date}', category_type = -2, hash = CONCAT('{\"-1\":\"-2\",', SUBSTRING(hash, 2, 255)) WHERE type = 'R' AND category_type IS NULL");
	Database::query("UPDATE gl_accounting_strip SET updated_at = '{$date}', category_type = -3, hash = CONCAT('{\"-1\":\"-3\",', SUBSTRING(hash, 2, 255)) WHERE type = 'I' AND category_type IS NULL");

	$type_category = ElementCategory::getOneAndLoad('id = -1');
	$type_meta_field_id = $type_category->getMetaFieldId();
	$type_map = ['expense_permissions' => 'E', 'revenue_permissions' => 'R', 'internal_permissions' => 'I'];
	$permissions = ERPPermission::getAllAndLoad("type IN ('expense_permissions', 'revenue_permissions', 'internal_permissions')");

	foreach($permissions as $id => $permission) {
		$type_id = $type_map[$permission->getType()];

		$permission_value = (new ERPPermissionValue())
			->setPermissionId($id)
			->setMetaFieldId($type_meta_field_id)
			->setOperator('=')
			->setValue($type_id)
			->setWildcardCharacter('X')
			->persist();
	}

	Database::query("UPDATE gl_permission SET type = 'accounting_strip_permissions' WHERE type IN ('expense_permissions', 'revenue_permissions', 'internal_permissions')");

	$tables = [
		'gl_batches',
		'gl_ap_request',
		'gl_ap_invoice',
		'gl_ba_checks',
		'gl_pos_receipt',
		'gl_pos_refund',
		'gl_pos_invoice',
		'gl_ar_deposit',
		'gl_bm_request',
		'gl_manual_journal_draft',
	];

	foreach($tables as $table) {
		if(!Database::columnExists($table, 'internal')) {
			Database::query("ALTER TABLE {$table} ADD internal BIGINT");
			Database::query("UPDATE {$table} SET internal = 0 WHERE internal IS NULL");
		}
	}

	// PO original_fiscal_year
	if(Database::$type === 'postgres') {
		Database::query("UPDATE gl_ap_request SET original_fiscal_year = date_part('year', request_date) - CASE WHEN date_part('month', request_date) < 7 THEN 1 ELSE 0 END");
	} else {
		Database::query("UPDATE gl_ap_request SET original_fiscal_year = datepart(year, request_date) - CASE WHEN datepart(month, request_date) < 9 THEN 1 ELSE 0 END");
	}

	/******************************************/
	/* Start queries for modifing permissions */
	/******************************************/

	if(!Database::columnExists('gl_permission_value', 'field')) {
		Database::query("ALTER TABLE gl_permission_value ADD field varchar(255)");
	}

	if(!Database::columnExists('gl_ap_approval_node', 'field')) {
		Database::query("ALTER TABLE gl_ap_approval_node ADD field varchar(255)");
	}

	$sql = "
		UPDATE
		    gl_ap_approval_node
		SET
		    field = gl_meta_field.name
		FROM
		    gl_meta_field
		JOIN
		    gl_meta_table
		ON
		    gl_meta_table.id = gl_meta_field.meta_table_id
		WHERE
		    gl_meta_field.id = gl_ap_approval_node.meta_field_id AND
		    gl_ap_approval_node.field IS NULL AND
		    gl_meta_field.id NOT IN (
		        SELECT meta_field_id FROM gl_element_category
		    )
	";
	Database::query($sql);

	$sql = "
		UPDATE
		    gl_permission_value
		SET
		    field = gl_meta_field.name
		FROM
		    gl_meta_field
		JOIN
		    gl_meta_table
		ON
		    gl_meta_table.id = gl_meta_field.meta_table_id
		WHERE
		    gl_meta_field.id = gl_permission_value.meta_field_id AND
		    gl_permission_value.field IS NULL AND
		    gl_meta_field.id NOT IN (
		        SELECT meta_field_id FROM gl_element_category
		    )
	";
	Database::query($sql);

	$sql = "
		UPDATE
		    gl_ap_approval_node
		SET
		    field = gl_element_category.id
		FROM
		    gl_element_category
		WHERE
		    gl_element_category.meta_field_id = gl_ap_approval_node.meta_field_id AND
		    gl_ap_approval_node.field IS NULL AND
		    gl_ap_approval_node.meta_field_id IN (
		        SELECT meta_field_id FROM gl_element_category
		    )
	";
	Database::query($sql);

	$sql = "
		UPDATE
		    gl_permission_value
		SET
		    field = gl_element_category.id
		FROM
		    gl_element_category
		WHERE
		    gl_element_category.meta_field_id = gl_permission_value.meta_field_id AND
		    gl_permission_value.field IS NULL AND
		    gl_permission_value.meta_field_id IN (
		        SELECT meta_field_id FROM gl_element_category
		    )
	";
	Database::query($sql);

	Database::query("DELETE FROM gl_permission_value WHERE field = 'amount'");

	/****************************/
	/* end updating permissions */
	/****************************/
}
