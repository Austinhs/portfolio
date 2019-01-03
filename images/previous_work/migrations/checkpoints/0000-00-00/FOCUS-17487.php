<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

MigrationFOCUS17487::addIndex('store_cart', 'owner_id');
MigrationFOCUS17487::addIndex('store_cart', 'transaction_number');
MigrationFOCUS17487::addIndex('store_cart_item', 'store_cart_id');
MigrationFOCUS17487::addIndex('store_item_component', 'store_item_id');

class MigrationFOCUS17487 {
	public static function addIndex($table, $column) {
		$index = "{$table}_{$column}_idx";

		if(!Database::indexExists($table, $index)) {
			Database::query("CREATE INDEX {$index} ON {$table} ({$column})");
		}
	}
}