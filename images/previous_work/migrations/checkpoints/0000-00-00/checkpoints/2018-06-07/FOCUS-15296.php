<?php

// Soft delete signatures that are not tied to any user and are not currently being used.

// SISStudent, SISUser, etc.
$classes = CustomFieldObject::getSubclasses();

// Loop over each CustomFieldObject subclass.
foreach($classes as $class) {
	// Get all signature fields (fortunately, signatures can't be used as log field columns).
	$fields  = $class::getFields(null, 'signature');

	$clauses = [];

	// Generate a join condition for each field that matches a signature record with the same ID in the column.
	foreach($fields as $field) {
		// The custom field ID of the signatures column.
		$field_id    = intval($field['id']);
		// The name of the signature column.
		$column_name = $field['column_name'];

		// Skip virtual fields
		if($field_id <= 0) {
			continue;
		}

		// Check to see if this field belongs to a form so we can check the right table.
		$is_form      = $class::isFormField($field_id);

		// If this field belongs to a form, get the name of the class belonging to the form.
		$record_class = $is_form ? $class::getFormClass() : $class;

		// Generate the clause that will be used to check if this column contains a signature ID so we do not delete it.
		$clauses[$record_class][] = Database::preprocess("s.id = CAST({{json_value:t.{$column_name}:id}} AS INT)");
	}

	$missing = null;

	// Get all signatures that are not referenced by any signature field in the object/form table
	foreach($clauses as $record_class => $tmp_clauses) {
		// Retrieve the table associated with SISStudents, SISUser, SISStudentFormRecord, etc.
		$table  = $record_class::$table;

		// For all the columns identified, generate SQL conditions to see if a signature ID is contained within this column.
		$conditions = '((' . join(') OR (', $tmp_clauses) . '))';

		// The query that will check if the signature id is not being referenced in any tables.
		$sql = "
			SELECT
				s.id
			FROM
				signatures s
			WHERE
				s.source_class IS NULL AND
				s.source_id IS NULL AND
				s.deleted IS NULL AND
				NOT EXISTS (
					SELECT 1 FROM signer_join_signatures sjs WHERE sjs.signature_id = s.id
				) AND
				NOT EXISTS (
					SELECT 1 FROM {$table} t WHERE ({$conditions})
				)
		";

		// All loops after $missing is set (even if it's empty).
		if(isset($missing)) {
			// On every loop after the first loop, pull missing IDs from other columns.
			$tmp_missing = array_column(Database::get($sql), 'ID');
			// We do not want to delete a signature if any other table is using a signature ID.
			// We intersect the arrays to only return IDs that are missing in ALL loops.
			$missing     = array_intersect($missing, $tmp_missing);
		}
		// First loop.
		else {
			// Create an array of missing signature IDs identified in the first loop.
			$missing     = array_column(Database::get($sql), 'ID');
		}
	}

	// Delete the missing signatures.
	if(!empty($missing)) {
		$missing_ids = join(', ', $missing);
		$sql         = "UPDATE signatures SET deleted = 1 WHERE id IN ({$missing_ids})";

		Database::query($sql);
	}
}
