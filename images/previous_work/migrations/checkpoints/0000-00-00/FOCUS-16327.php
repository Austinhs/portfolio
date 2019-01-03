<?php

if (empty($GLOBALS['FocusFinanceConfig']['enabled'])) {
	return false;
}
	//svn merge svn://focus-sis.com/focus/branches/8.0/dev/FOCUS-16327 -r247339:HEA

	Database::begin();

	//Add Categories
	$cat = CustomFieldCategory::getOneAndLoad("source_class = 'FocusUser' AND title = 'Personnel Evaluation'");
	$cat_id = $cat->getId();

	$temp = CustomFieldJoinCategory::getOneAndLoad("category_id = ".$cat_id);
	$field_id = $temp->getFieldId();

	$log_columns = [
		[
			'title' => 'Leadership',
			'type' => 'numeric',
			'col_name' => 'leadership',
			'system' => 1,
			'sort_order' => 100
		],
		[
			'title' => 'Practice',
			'type' => 'numeric',
			'col_name' => 'practice',
			'system' => 1,
			'sort_order' => 200
		],
		[
			'title' => 'Responsibilities',
			'type' => 'numeric',
			'col_name' => 'responsibilities',
			'system' => 1,
			'sort_order' => 300
		],
		[
			'title' => 'Performance',
			'type' => 'numeric',
			'col_name' => 'performance',
			'system' => 1,
			'sort_order' => 400
		],
		[
			'title' => 'Measures',
			'type' => 'select',
			'col_name' => 'measures',
			'system' => 1,
			'sort_order' => 500
		]
	];

	$options = [
		[
			'code'  => 'B',
			'label' => '[B] Exclusively (100%) on district-developed or district-selected end-of-course assessments'
		],
		[
			'code'  => 'C',
			'label' => '[C] Exclusively (100%) on other standardized assessments, including nationally recognized standardized assessments'
		],
		[
			'code'  => 'D',
			'label' => '[D] Exclusively (100%) on industry certification examinations'
		],
		[
			'code'  => 'E',
			'label' => '[E] Exclusively (100%) on measurable learning targets / student learning objectives'
		],
		[
			'code'  => 'F',
			'label' => '[F] Combination of assessments, with the state assessments accounting for the largest component'
		],
		[
			'code'  => 'G',
			'label' => '[G] Combination of assessments, with the state assessments not accounting for the largest component'
		],
		[
			'code'  => 'H',
			'label' => '[H] The classroom teacher or school administrator was not evaluated.'
		],
		[
			'code'  => 'I',
			'label' => '[I] Combination of assessments, no state assessments.'
		],
		[
			'code'  => 'J',
			'label' => '[J] Exclusively (100%) statewide VAM models'
		],
		[
			'code'  => 'K',
			'label' => '[K] Exclusively (100%) on statewide assessments without VAM models'
		],
			[
			'code'  => 'Z',
			'label' => '[Z] Not a classroom teacher or school administrator'
		]
	];

	foreach ($log_columns as $log_column) {
		$lc = new CustomFieldLogColumn();
		$lc->setFieldId($field_id);
		$lc->setTitle($log_column['title']);
		$lc->setType($log_column['type']);
		$lc->setSystem($log_column['system']);
		$lc->setSortOrder($log_column['sort_order']);
		$lc->persist();
		if($log_column['title'] == 'Measures')
		{
			$column_id   = intval($lc->getId());
			$new_options = [];

			foreach($options as $option) {
				$new_option = new CustomFieldSelectOption();

				$new_option
					->setSourceClass('CustomFieldLogColumn')
					->setSourceId($column_id)
					->setRecord($option);

				$new_options[] = $new_option;
			}

			CustomFieldSelectOption::insert($new_options);
		}
	}

	Database::query("update custom_field_log_columns set system = 1 where field_id = {$field_id}");


	Database::commit();


