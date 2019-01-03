<?php

Database::query("
	UPDATE
		course_periods
	SET
		mp = 'FY'
	WHERE
		syear >= '2017'
		AND mp != 'FY'
		AND marking_period_id = '0'
");

Database::query("
	UPDATE
		course_periods
	SET
		mp = 'SEM'
	WHERE
		syear >= '2017'
		AND mp != 'SEM'
		AND EXISTS(
			SELECT
				1
			FROM
				school_semesters ss
			WHERE
				ss.syear = course_periods.syear
				AND ss.school_id = course_periods.school_id
				AND ss.marking_period_id = course_periods.marking_period_id
		)
");

Database::query("
	UPDATE
		course_periods
	SET
		mp = 'QTR'
	WHERE
		syear >= '2017'
		AND mp != 'QTR'
		AND EXISTS(
			SELECT
				1
			FROM
				school_quarters sq
			WHERE
				sq.syear = course_periods.syear
				AND sq.school_id = course_periods.school_id
				AND sq.marking_period_id = course_periods.marking_period_id
		)
");

Database::query("
	UPDATE
		schedule
	SET
		mp = 'FY'
	WHERE
		syear >= '2017'
		AND mp != 'FY'
		AND marking_period_id = '0'
");

Database::query("
	UPDATE
		schedule
	SET
		mp = 'SEM'
	WHERE
		syear >= '2017'
		AND mp != 'SEM'
		AND EXISTS(
			SELECT
				1
			FROM
				school_semesters ss
			WHERE
				ss.syear = schedule.syear
				AND ss.school_id = schedule.school_id
				AND ss.marking_period_id = schedule.marking_period_id
		)
");

Database::query("
	UPDATE
		schedule
	SET
		mp = 'QTR'
	WHERE
		syear >= '2017'
		AND mp != 'QTR'
		AND EXISTS(
			SELECT
				1
			FROM
				school_quarters sq
			WHERE
				sq.syear = schedule.syear
				AND sq.school_id = schedule.school_id
				AND sq.marking_period_id = schedule.marking_period_id
		)
");