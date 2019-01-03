<?php
// Tags: Formbuilder
if (Database::$type === "postgres") {
	$query = "ALTER TABLE sss_form_instances ALTER column student_id TYPE numeric USING student_id::numeric";
} else {
	$query = "ALTER TABLE sss_form_instances ALTER column student_id numeric";
}

// MS SQL is retarded so we drop/create constraints
if (Database::$type === "postgres") {
	$exists = "IF EXISTS";
}

Database::query("DROP INDEX {$exists} sss_form_instances_student_id_ind" . (Database::$type === "postgres" ? "" : " ON sss_form_instances"));
Database::query("ALTER TABLE sss_form_instances DROP CONSTRAINT {$exists} sss_form_instances_student_id_foreign");
Database::query($query);
Database::query("CREATE INDEX sss_form_instances_student_id_ind ON sss_form_instances(student_id)");
Database::query("ALTER TABLE sss_form_instances ADD CONSTRAINT sss_form_instances_student_id_foreign FOREIGN KEY (student_id) REFERENCES students(student_id)");
