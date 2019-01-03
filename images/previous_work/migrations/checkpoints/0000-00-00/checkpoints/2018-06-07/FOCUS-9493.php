<?php

if (!Database::columnExists("test_history_score_ranges", "form")) {
	Database::query("ALTER TABLE test_history_score_ranges ADD form VARCHAR(255)");
}

if (Database::columnExists("test_history_score_ranges", "level_id")) {
	Database::renameColumn("level_id", "level", "test_history_score_ranges");
	Database::changeColumnType("test_history_score_ranges", "level", "varchar");
}

// The columns are all not nullable on mssql for some reason. They need to be nullable
if (Database::$type === 'mssql') {
	Database::query("ALTER TABLE test_history_score_ranges ALTER COLUMN gradelevel varchar(2) NULL");
	Database::query("ALTER TABLE test_history_score_ranges ALTER COLUMN max numeric(18) NULL");
	Database::query("ALTER TABLE test_history_score_ranges ALTER COLUMN min numeric(18) NULL");
	Database::query("ALTER TABLE test_history_score_ranges ALTER COLUMN part_id float(53) NULL");
	Database::query("ALTER TABLE test_history_score_ranges ALTER COLUMN score_type_id numeric(18) NULL");
	Database::query("ALTER TABLE test_history_score_ranges ALTER COLUMN level varchar(255) NULL");
}
