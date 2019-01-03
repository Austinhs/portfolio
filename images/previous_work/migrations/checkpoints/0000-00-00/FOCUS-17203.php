<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

//svn merge svn://focus-sis.com/focus/branches/8.0/dev/FOCUS-17203 -r264846:HEAD
Database::begin();

Database::query("
	update custom_fields
	set deleted = 1
	where title like '%Multidistrict%'
	and deleted is null
	and type <> 'log'
");

//Data for fields
$fields = [
	[
		'title' => 'Multidistrict Employee',
		'column' => 'multidistrict_employee',
		'type' => 'log'
	]
];

//Get Category info
$cat = CustomFieldCategory::getOneAndLoad("source_class = 'FocusUser' AND title = 'State Reporting'");
$cat_id = $cat->getId();

$sort_increment = 0;
foreach ($fields as $field) {
	if (empty(FocusUser::getFieldByAlias($field['column']))) {
		//Add field
		$cf = new CustomField();
		$cf->setTitle($field['title']);
		$cf->setAlias($field['column']);
		$cf->setType($field['type']);
		$cf->setSourceClass('FocusUser');
		if (!empty($field['option_query'])) {
			$cf->setOptionQuery($field['option_query']);
		}
		if (!empty($field['new'])) {
			$cf->setNewRecord(1);
		}
		$cf->setSystem(1);
		$cf->persist();

		//Get Field info
		$field_id = $cf->getId();

		//Join to category
		$cfjc = new CustomFieldJoinCategory();
		$cfjc->setCategoryId($cat_id);
		$cfjc->setFieldId($field_id);
		$cfjc->setSortOrder(8675309+$sort_increment);
		$cfjc->persist();

		$sort_increment++;

	}
}

$cfjc = new CustomFieldJoinCategory();
$cfjc->fixSortOrders();

$log_columns = [
	[
		'title' => 'District#',
		'type' => 'numeric',
		'col_name' => 'district',
		'system' => 1,
		'sort_order' => 100
	],
	[
		'title' => 'Assignment Identifier',
		'type' => 'select',
		'col_name' => 'assignment',
		'system' => 1,
		'sort_order' => 200
	]
];

$options = [
	[
		'code'  => 'X',
		'label' => 'X: Multidistrict consortium employee'
	],
	[
		'code'  => 'Y',
		'label' => 'Y: Employed in more than one district'
	],
];

foreach ($log_columns as $log_column) {
	$lc = new CustomFieldLogColumn();
	$lc->setFieldId($field_id);
	$lc->setTitle($log_column['title']);
	$lc->setType($log_column['type']);
	$lc->setSystem($log_column['system']);
	$lc->setSortOrder($log_column['sort_order']);
	$lc->persist();

	if($log_column['col_name'] == "assignment")
	{

		$lc
			->setRequired(1)
			->persist();

		foreach($options as $option) {
		$new_option = new CustomFieldSelectOption();

			$new_option
				->setSourceClass('CustomFieldLogColumn')
				->setSourceId($lc->getId())
				->setRecord($option);
			$new_options[] = $new_option;
		}

		CustomFieldSelectOption::insert($new_options);
	}

	if($log_column['col_name'] == "district")
	{
		$lc
			->setMaxLength(2)
			->setRequired(1)
			->persist();
	}

}

Database::query("update custom_field_log_columns set system = 1 where field_id = {$field_id}");

Database::commit();

return true;
?>
