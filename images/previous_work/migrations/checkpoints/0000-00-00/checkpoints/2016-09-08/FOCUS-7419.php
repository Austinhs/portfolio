<?php

if(!Database::columnExists('edit_rules', 'match_sql')) {
	Database::createColumn('edit_rules', 'match_sql', 'text');
}

$make_rule = !empty($GLOBALS['_FOCUS']['config']['STUDENT_ENROLLMENT']['CUSTOM_9']);

// For all districts with "student_enrollment.custom_9", add a rule for Zendesk #68927
if($make_rule && Database::columnExists('student_enrollment', 'custom_9')) {
	$sql = "SELECT
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
			WHERE
				COALESCE(se.custom_9, 'N') = 'N'
				AND COALESCE(se2.custom_9, 'N') = 'N'
				-- Remove this to evaluate all school years
				 AND se.syear = {syear}
	";

	$rule = [
		'name'            => "Multiple Active Enrollments",
		'message'         => "This student has more than one active enrollment record without the \"Second School\" field marked.",
		'enabled'         => 1,
		'prevents_saving' => 1,
		'category'        => 'SISStudent',
		'system'          => 1,
		'type'            => 'validation',
		'match_sql'       => $sql
	];

	$where = [
		'name = :name'
	];

	$params = [
		'name' => $rule['name']
	];

	$rule_obj = EditRule::getOne($where, null, $params);

	if(empty($rule_obj)) {
		$rule_obj = new EditRule();

		$rule_obj
			->setRecord($rule)
			->persist();
	}

	$criterion = [
		'field1'   => 'enrollment|custom_9',
		'type'     => 'checkbox',
		'value'    => 'Y',
		'rule_id'  => $rule_obj->getId(),
		'reversed' => 1
	];

	$where = [
		'rule_id = :rule_id',
		'field1 = :field1'
	];

	$params = [
		'rule_id' => $criterion['rule_id'],
		'field1'  => $criterion['field1']
	];

	$criterion_obj = EditRuleCriterion::getOne($where, null, $params);

	if(empty($criterion_obj)) {
		$criterion_obj = new EditRuleCriterion();

		$criterion_obj
			->setRecord($criterion)
			->persist();
	}
}
