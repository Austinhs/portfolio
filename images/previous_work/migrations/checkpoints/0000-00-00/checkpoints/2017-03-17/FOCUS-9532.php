<?php

Database::query("
	UPDATE
		student_report_card_grades
	SET
		report_card_grade_id = gr.id,
		gpa_points = gr.gpa_value,
		weighted_gpa_points = gr.weighted_gpa_value
	FROM
		report_card_grades gr
	WHERE
		gr.scale_id = student_report_card_grades.grade_scale_id AND
		gr.title = student_report_card_grades.grade_title AND
		student_report_card_grades.syear = 2016 AND
		student_report_card_grades.marking_period_id NOT LIKE 'DT%' AND
		COALESCE(student_report_card_grades.gpa_points, 999) <> gr.gpa_value
");
