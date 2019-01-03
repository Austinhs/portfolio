<?php

Migrations::depend('FOCUS-6197');

//Drop Date should be empty for Fed State 63
$result          = Database::get("SELECT ID FROM EDIT_RULES WHERE NAME LIKE '%Fed State 63%'");
$fed_state_63_id = !empty($result) ? $result[0]['ID'] : null;

if ($fed_state_63_id) {

	$criterion = [
		'field1'   => 'enrollment|end_date',
		'type'     => 'date',
		'value'    => '',
		'rule_id'  => $fed_state_63_id
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