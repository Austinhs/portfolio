<?php

Migrations::depend('FOCUS-6359');
Migrations::depend('FOCUS-5468');

if(!class_exists('CustomFieldObject') || !Database::columnExists('custom_fields', 'legacy_id')) {
	throw new Exception("This upgrade cannot be run on this version of Focus");
}

// Make the field 255 characters
Database::changeColumnType('letters', 'display_location', 'varchar');

// Link to the new Custom Field Categories
$sql = Database::preprocess("
	UPDATE
		letters
	SET
		display_location = CONCAT('CustomFieldCategory:', cfc.id)
	FROM
		custom_field_categories cfc
	WHERE
		({{is_int:letters.display_location}}) AND
		cfc.source_class = 'SISStudent' AND
		cfc.legacy_id = CAST(letters.display_location AS BIGINT)
");

Database::query($sql);

// Add the 'email' and 'letter_log' aliases for students
$aliases = [
	200000012 => 'email',
	200000014 => 'letter_log'
];

foreach($aliases as $legacy_id => $alias) {
	$sql = "
		UPDATE
			custom_fields
		SET
			alias = :alias,
			system = 1
		WHERE
			source_class = :class AND
			legacy_id = :legacy_id
	";

	$params = [
		'class'     => 'SISStudent',
		'legacy_id' => intval($legacy_id),
		'alias'     => $alias
	];

	Database::query($sql, $params);
}

$sql = "
	UPDATE
		custom_field_log_columns
	SET
		system = 1
	FROM
		custom_fields cf
	WHERE
		cf.alias = :alias AND
		cf.source_class = :class AND
		custom_field_log_columns.field_id = cf.id
";

$params = [
	'class' => 'SISStudent',
	'alias' => $alias
];

Database::query($sql, $params);

// Refresh the student views
SISStudent::refreshViews();
