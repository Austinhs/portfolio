<?php
// Tags: Formbuilder
if (Database::$type === 'mssql') {
	$identity = Database::get("SELECT 1 FROM sys.identity_columns WHERE OBJECT_NAME(object_id) = 'sss_form_collections'");
	if (!empty($identity)) {
		Database::createSequence('sss_form_collections_id_seq');
		Database::query("ALTER TABLE sss_form_collections ADD tmp_id BIGINT DEFAULT NEXT VALUE FOR sss_form_collections_id_seq");
		Database::query("UPDATE sss_form_collections SET tmp_id = id");
		$constraints = Database::getConstraints("sss_form_collections");
		foreach ($constraints as $constraint_name => $value) {
			Database::query("ALTER TABLE sss_form_collections DROP CONSTRAINT {$constraint_name}");
		}
		Database::query("ALTER TABLE sss_form_collections drop column id");
		Database::renameColumn('tmp_id', 'id', 'sss_form_collections');
	}
}
