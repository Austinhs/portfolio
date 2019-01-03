<?php

Migrations::depend('FOCUS-15787');
Migrations::depend('FOCUS-14324');

$where = [
	'name = :name'
];

$params = [
	'name' => 'Multiple Active Enrollments'
];

$rule = EditRule::getOne($where, null, $params);

if(!empty($rule)) {
	$sql         = $rule->getMatchSql();
	$curr_date   = (Database::$type == 'postgres') ? 'current_date' : 'getdate()';
	$sql         = "
	SELECT
		s.student_id
	FROM
		students s
		JOIN student_enrollment se ON
		se.student_id = s.student_id
		JOIN student_enrollment se2 ON
		se.student_id = se2.student_id
		AND se.syear = se2.syear
		AND (
		se2.start_date BETWEEN se.start_date AND COALESCE(se.end_date, '9999-12-31')
		OR COALESCE(se2.end_date, '9999-12-31') BETWEEN se.start_date AND COALESCE(se.end_date, '9999-12-31')
		OR se.start_date BETWEEN se2.start_date AND COALESCE(se2.end_date, '9999-12-31')
		)
		AND se.id != se2.id
		AND
			(
				(se.end_date >= {$curr_date} OR se.end_date IS NULL)
				AND (se2.end_date >= {$curr_date} OR se2.end_date IS NULL)
		)
	WHERE
		COALESCE(se.custom_9, 'N') = 'N'
		AND COALESCE(se2.custom_9, 'N') = 'N'
		AND se.syear = {syear}";

	$rule
		->setMatchSql($sql)
		->persist();
}