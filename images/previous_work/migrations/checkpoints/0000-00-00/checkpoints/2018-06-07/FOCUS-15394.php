<?php

if (Database::indexExists("school_choice_tours_auditions", "scta_student")) {
	$sql = "DROP INDEX scta_student {{mssql: ON school_choice_tours_auditions}}";
	$sql = Database::preprocess($sql);

	Database::query($sql);
}

if (Database::indexExists("school_choice_tours_auditions", "scta_school")) {
	$sql = "DROP INDEX scta_school {{mssql: ON school_choice_tours_auditions}}";
	$sql = Database::preprocess($sql);

	Database::query($sql);
}

if (Database::columnExists("school_choice_tours_auditions", "student")) {
	Database::query("UPDATE school_choice_tours_auditions SET student = NULL WHERE student = ''");

	Database::changeColumnType("school_choice_tours_auditions", "student", "bigint");
	Database::renameColumn("student", "student_id", "school_choice_tours_auditions");
}

if (!Database::indexExists("school_choice_tours_auditions", "scta_student_id_school")) {
	$sql =
		"CREATE INDEX
			scta_student_id_school
		ON
			school_choice_tours_auditions (student_id, school)";

	Database::query($sql);
}
