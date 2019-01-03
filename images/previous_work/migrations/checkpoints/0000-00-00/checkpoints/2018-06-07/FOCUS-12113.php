<?php

if(Database::$type === 'mssql') {
	Database::query("DROP FUNCTION CalcFilledSeats");

	Database::query("
		CREATE FUNCTION CalcFilledSeats (
			@cp_id numeric, @syear numeric, @school_id numeric,
			@effective_date date = NULL
		) RETURNS bigint AS BEGIN RETURN (
			SELECT
				COUNT(*)
			FROM
				schedule s,
				student_enrollment se
			WHERE
				s.student_id = se.student_id
				AND se.syear = s.syear
				AND se.school_id = s.school_id
				AND se.syear = @syear
				AND se.school_id = @school_id
				AND s.course_period_id = @cp_id
				AND (
					se.end_date IS NULL OR
					ISNULL(@effective_date,CURRENT_TIMESTAMP) BETWEEN se.start_date AND se.end_date
				)
				AND (
					s.end_date IS NULL OR
					ISNULL(@effective_date,CURRENT_TIMESTAMP) BETWEEN s.start_date AND s.end_date
				)
		) END;
	");
}
else if(Database::$type === 'postgres') {
	$existing_function = Database::get("
		SELECT
			COUNT('') DATE_PARAM
		FROM
			information_schema.routines
			JOIN information_schema.parameters ON information_schema.parameters.specific_name = information_schema.routines.specific_name
		WHERE
			LOWER(routine_name) = 'calcfilledseats'
			AND information_schema.parameters.data_type = 'date'
		");

	$date_param = $existing_function[0]['DATE_PARAM'] == 0 ? '' : ',date';

	Database::query("DROP FUNCTION IF EXISTS CalcFilledSeats (numeric,numeric,numeric {$date_param})");

	Database::query("
		CREATE FUNCTION CalcFilledSeats (numeric, numeric, numeric, date=CURRENT_DATE) RETURNS bigint AS
		'(SELECT count(*)
			FROM
				schedule s,
				student_enrollment se
			WHERE s.student_id = se.student_id
				AND se.syear = s.syear
				AND se.school_id = s.school_id
				AND se.syear = $2
				AND se.school_id = $3
				AND s.course_period_id = $1
				AND (
					se.end_date IS NULL OR
					$4 BETWEEN se.start_date AND se.end_date
				)
				AND (
					s.end_date IS NULL OR
					$4 BETWEEN s.start_date AND s.end_date
				))'
			language SQL"
		);
}

