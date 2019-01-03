<?php

$tables = [
	"password_history" => [
		"source_id"    => ["BIGINT"],
		"source_class" => ["VARCHAR", 255],
		"username"     => ["VARCHAR", 255],
		"password"     => ["VARCHAR", 255],
		"created_at"   => ["TIMESTAMP"],
	],
];

foreach($tables as $table => $columns) {
	if(!Database::tableExists("{$table}")) {
		Database::query("CREATE TABLE {$table} (id BIGINT PRIMARY KEY)");
	}

	if(!Database::sequenceExists("{$table}_seq")) {
		Database::createSequence("{$table}_seq");
	}

	foreach($columns as $column => $definition) {
		$type     = array_shift($definition);
		$length   = array_shift($definition);
		$nullable = array_shift($definition);

		if($nullable === null) {
			$nullable = true;
		}

		if(!Database::columnExists($table, $column)) {
			Database::createColumn($table, $column, $type, $length, $nullable);
		}
	}
}

$sequence = Database::nextSql("password_history_seq");

Database::query(Database::preprocess("
	INSERT INTO password_history (
		id,
		source_id,
		source_class,
		username,
		password,
		created_at
	)
	SELECT
		{$sequence},
		u.staff_id,
		'SISUser' AS source_class,
		u.username,
		u.password,
		CURRENT_TIMESTAMP AS created_at
	FROM
		users u
	WHERE
		u.username IS NOT NULL AND
		u.password IS NOT NULL AND
		NOT EXISTS (
			SELECT
				NULL
			FROM
				password_history
			WHERE
				password_history.username = u.username AND
				password_history.password = u.password
		)
"));

Database::query(Database::preprocess("
	INSERT INTO password_history (
		id,
		source_id,
		source_class,
		username,
		password,
		created_at
	)
	SELECT
		{$sequence},
		s.student_id,
		'SISStudent' AS source_class,
		s.username,
		s.password,
		CURRENT_TIMESTAMP AS created_at
	FROM
		students s
	WHERE
		s.username IS NOT NULL AND
		s.password IS NOT NULL AND
		NOT EXISTS (
			SELECT
				NULL
			FROM
				password_history
			WHERE
				password_history.username = s.username AND
				password_history.password = s.password
		)
"));
