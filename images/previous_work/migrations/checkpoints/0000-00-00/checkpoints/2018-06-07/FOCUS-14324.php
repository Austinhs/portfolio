<?php

Migrations::depend('FOCUS-15787');

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
	$replacement = "AND se.id != se2.id
		AND (
			(se.end_date >= {$curr_date} OR se.end_date IS NULL)
			AND (se2.end_date >= {$curr_date} OR se2.end_date IS NULL)
		)";

	if(strpos($sql, $replacement) !== false) {
		$sql = str_replace('AND se.id != se2.id', $replacement, $sql);

		$rule
			->setMatchSql($sql)
			->persist();
	}
}