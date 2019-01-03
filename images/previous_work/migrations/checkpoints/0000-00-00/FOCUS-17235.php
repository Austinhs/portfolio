<?php
$table       = 'database_object_log';
$student_col = 'student_id';

if(Database::tableExists($table)) {
	Database::query("DELETE FROM database_object_log WHERE CAST(log_time as date) < CAST('2018-01-01' as date)");

	if(!Database::columnExists($table, $student_col)) {
		Database::createColumn($table, $student_col, 'bigint');
	}

	function update_dbo_log($condition) {
		if(!empty($condition)) {

			for($i = 0; $i < 10; $i++) {
				$sql = "
					UPDATE
						database_object_log
					SET
						student_id = t.student_id
					FROM (
						SELECT DISTINCT
							dol.id,
							dol.record_class,
							FIRST_VALUE(COALESCE(
								CAST(({{json_value:dol.after:STUDENT_ID}}) as BIGINT),
								CAST(({{json_value:dol.before:STUDENT_ID}}) as BIGINT),
								CAST(({{json_value:dol2.after:STUDENT_ID}}) as BIGINT),
								CAST(({{json_value:dol2.before:STUDENT_ID}}) as BIGINT),
								CAST(se.student_id as BIGINT)
							)) OVER (PARTITION BY dol.id ORDER BY COALESCE(dol2.id, -1) DESC) AS student_id
						FROM
							database_object_log dol LEFT JOIN
							database_object_log dol2 ON
								dol.action = 'UPDATE' AND
								dol2.id < dol.id AND
								dol2.record_class = dol.record_class AND
								dol2.record_id = dol.record_id LEFT JOIN
							student_enrollment se ON
								se.id = dol.record_id
						WHERE
							{$condition} AND
							dol.id % 10 = :i AND
							COALESCE(
								CAST(({{json_value:dol.after:STUDENT_ID}}) as BIGINT),
								CAST(({{json_value:dol.before:STUDENT_ID}}) as BIGINT),
								CAST(({{json_value:dol2.after:STUDENT_ID}}) as BIGINT),
								CAST(({{json_value:dol2.before:STUDENT_ID}}) as BIGINT),
								CAST(se.student_id as BIGINT)
							) IS NOT NULL
					) t
					WHERE
						t.student_id IS NOT NULL AND
						database_object_log.id = t.id
				";
				$sql = Database::preprocess($sql);

				Database::query($sql, ['i' => $i]);
			}
		}
	}
	update_dbo_log("dol.record_class = 'StudentEnrollment'");
	update_dbo_log("dol.record_class = 'StudentReportCardGrade'");
	update_dbo_log("dol.record_class != 'StudentEnrollment' AND dol.record_class != 'StudentReportCardGrade'");

	$sql = "
		UPDATE
			database_object_log
		SET
			student_id = record_id
		WHERE
			record_class = 'SISStudent'
	";

	Database::query($sql);

	if(!Database::indexExists($table, 'database_object_log_student_id_index')) {
		Database::query("CREATE INDEX database_object_log_student_id_index ON database_object_log(student_id)");
	}
}