<?php

// Tags: SSS, Formbuilder
if (Database::columnExists('sss_form_instances', 'staff_id')) {
	$length = '';

	if (Database::$type === "mssql") {
		// Microsoft is dumb, so we must recreate the index even though the btree will remain the same. Postgres is smart and knows this.
		if (Database::indexExists('sss_form_instances', 'sss_form_instances_staff_id_ind')) {
			Database::query("DROP INDEX sss_form_instances_staff_id_ind ON sss_form_instances");
		}

		// Microsoft is dumb and makes us recreate the foreign key. Postgres is smart and does not.
		$foreignKeys = Database::getForeignKeys('sss_form_instances', 'staff_id', false);
		if (!empty($foreignKeys)) {
			Database::query("ALTER TABLE sss_form_instances DROP CONSTRAINT sss_form_instances_staff_id_foreign");
		}

		$length = 18;
	}

	Database::changeColumnType('sss_form_instances', 'staff_id', 'numeric', $length);

	if (!Database::indexExists('sss_form_instances', 'sss_form_instances_staff_id_ind')) {
		Database::query("CREATE INDEX sss_form_instances_staff_id_ind ON sss_form_instances(staff_id)");
	}

	if (Database::$type === "mssql") {
		Database::query("ALTER TABLE sss_form_instances ADD CONSTRAINT sss_form_instances_staff_id_foreign FOREIGN KEY (staff_id) REFERENCES users(staff_id)");
	}
}
