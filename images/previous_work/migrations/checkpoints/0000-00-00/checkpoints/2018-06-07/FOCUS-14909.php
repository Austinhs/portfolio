<?php

// Check if constraint exists.
$constraints       = Database::getConstraints('custom_reports_variables');
$constraint_exists = isset($constraints['unique_package_variable']);

if(!$constraint_exists) {
	// Delete 'older' duplicates before adding the constraint.
	Database::query("
		WITH max AS (
			SELECT id, MAX(id) OVER (PARTITION BY package, variable_name) AS max_id FROM custom_reports_variables
		)
		DELETE FROM custom_reports_variables WHERE id IN (SELECT id FROM max WHERE id != max_id)
	");

	// Add the constraint.
	Database::query("ALTER TABLE custom_reports_variables ADD CONSTRAINT unique_package_variable UNIQUE (package, variable_name)");
}
