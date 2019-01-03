<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_sequences", "class")) {
	// Make sure gl_sequences.title is nullable
	Database::changeColumnType('gl_sequences', 'title', 'varchar', '255', true);

	Database::createColumn("gl_sequences", "class", "VARCHAR");
	Database::createColumn("gl_sequences", "include_fiscal", "BIGINT");
	Database::createColumn("gl_sequences", "prefix", "VARCHAR");
	Database::createColumn("gl_sequences", "source_types", "VARCHAR");
	Database::createColumn("gl_sequences", "application_level", "CHAR");

	$sql =
		"UPDATE
			gl_sequences
		SET
			class = 'Sequence',
			application_level = 'B'";

	Database::query($sql);

	$current  = Sequence::current("PO");
	$types    = json_encode(array_keys(Request::getRequestTypes()));
	$sequence = (new RequestSequence)
		->setSourceTypes($types)
		->setSeq($current)
		->setApplicationLevel("B")
		->persist();
}

Database::commit();
return true;
?>