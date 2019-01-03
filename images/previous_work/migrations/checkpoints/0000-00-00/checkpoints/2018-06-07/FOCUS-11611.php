<?php

// $error_reporting = error_reporting(E_ALL);
// $display_errors  = ini_set('display_errors', 1);
// $GLOBALS['disable_login_redirect'] = true;
// require_once('../Warehouse.php');

$state_name = null;
if(!empty($GLOBALS['_FOCUS']['config']['state_name'])) {
	$state_name = strtolower($GLOBALS['_FOCUS']['config']['state_name']);
}

if($state_name === 'texas'){
	return;
}

Database::begin();


	if (Database::tableExists('grad_requirements_category')) {

		$requirement_category = GradRequirementsCategory::getOne([
			"CLASS_NAME = 'BiliteracySealCategoryGradRequirementsReport'"
		]);

		if(empty($requirement_category)) {

			$new_category = new GradRequirementsCategory();
			$new_category
						->setTitle('Biliteracy Seal')
						->setTemplate('biliteracy')
						->setClassName('BiliteracySealCategoryGradRequirementsReport')
						->persist();
		}
	}

	if (Database::tableExists('grad_requirements')) {

		if(!Database::columnExists('grad_requirements', 'parent_id')){
			Database::createColumn('grad_requirements', 'parent_id', 'BIGINT');
		}

		$grad_requirements_category_id = "(select id from grad_requirements_category where template = 'biliteracy')";

		if($grad_requirements_category_id){

			$requirements = GradRequirements::getOne([
				"GRAD_REQUIREMENTS_CATEGORY_ID = {$grad_requirements_category_id}"
			]);

			if(!$requirements){
				$sql = Database::preprocess("
						INSERT INTO grad_requirements
							({{postgres:ID,}} title, \"rule\", grad_requirements_category_id, parent_id)
						VALUES
							({{postgres:{{next:grad_requirements_seq}},}} 'Gold', 'evaluateGold', {$grad_requirements_category_id}, null),
							({{postgres:{{next:grad_requirements_seq}},}} 'Silver', 'evaluateSilver', {$grad_requirements_category_id}, null)

				");
				Database::query($sql);

				$mssql_escape = "";
				if(Database::$type === "mssql"){
					$mssql_escape = "\"";
				}

				$gold_requirement_id = GradRequirements::getOne([
					"{$mssql_escape}RULE{$mssql_escape} = 'evaluateGold'"
				])->getId();

				$silver_requirement_id = GradRequirements::getOne([
					"{$mssql_escape}RULE{$mssql_escape} = 'evaluateSilver'"
				])->getId();

				$sql = Database::preprocess("
						INSERT INTO grad_requirements
							({{postgres:ID,}} title, \"rule\", grad_requirements_category_id, parent_id)
						VALUES
							({{postgres:{{next:grad_requirements_seq}},}} 'Earned 4 credits in same foreign language with cum GPA 3.0 or higher', 'EarnedFourCreditsForeignLanguage', {$grad_requirements_category_id}, {$silver_requirement_id}),

							({{postgres:{{next:grad_requirements_seq}},}} 'Minimum score on nationally recognized foreign language assessment', 'MinimumScoreNationallyRecognizedAssessmentSilver', {$grad_requirements_category_id}, {$silver_requirement_id}),

							({{postgres:{{next:grad_requirements_seq}},}} 'Portfolio Option at Intermediate Mid level or higher', 'PortfolioOptionIntermediateMidlevelOrHigher', {$grad_requirements_category_id}, {$silver_requirement_id}),

							({{postgres:{{next:grad_requirements_seq}},}} 'Earned 4 credits in same foreign language with cum GPA 3.0 or higher and Level 4 or higher on the grade 10 ELA FSA', 'EarnedFourCreditsForeignLanguageAndLevelFourELASFA', {$grad_requirements_category_id}, {$gold_requirement_id}),

							({{postgres:{{next:grad_requirements_seq}},}} 'Minimum score on nationally recognized foreign language assessment', 'MinimumScoreNationallyRecognizedAssessmentGold', {$grad_requirements_category_id}, {$gold_requirement_id}),

							({{postgres:{{next:grad_requirements_seq}},}} 'Portfolio Option at Advanced Low level or higher', 'PortfolioOptionAdvancedLowlevelOrHigher', {$grad_requirements_category_id}, {$gold_requirement_id})
				");

				Database::query($sql);
			}
		}
	}

	//Checking all custom_fields exists
	$fields = [
		[
			"column_name" => "custom_fl_biliteracy_asl",
			"alias"       => "slpi_asl",
			"title"       => "Sign Language Proficiency Interview : American Sign Language (SLPI:ASL)",
			"type"        => "select"
		],
		[
			"column_name" => "custom_fl_biliteracy_aappl",
			"alias"       => "actfl_aappl",
			"title"       => "ACTFL Assessment of Performance Toward Proficiency in Language (AAPPL) ",
			"type"        => "select"
		],
		[
			"column_name" => "custom_fl_biliteracy_opi",
			"alias"       => "actfl_opi",
			"title"       => "ACTFL Oral Proficiency Interview (OPI)",
			"type"        => "select"
		],
		[
			"column_name" => "custom_fl_biliteracy_stamp4s",
			"alias"       => "stamp4s",
			"title"       => "Standards-based Measurement of Proficiency for Grade 7-Adult (STAMP4S)",
			"type"        => "select"
		],
		[
			"column_name" => "custom_fl_biliteracy_lira",
			"alias"       => "actfl_lira",
			"title"       => "ACTFL Latin Interpretive Reading Assessment (LIRA)",
			"type"        => "select"
		]
	];

	$fields_options = [
		[
			"code"   => "D",
			"label"  => "Distinguished",
			"weight" => 11
		],
		[
			"code"   => "S",
			"label"  => "Superior",
			"weight" => 10
		],
		[
			"code"   => "AH",
			"label"  => "Advanced High",
			"weight" => 9
		],
		[
			"code"   => "AM",
			"label"  => "Advanced Mid",
			"weight" => 8
		],
		[
			"code"   => "AL",
			"label"  => "Advanced Low",
			"weight" => 7
		],
		[
			"code"   => "IH",
			"label"  => "Intermediate High",
			"weight" => 6
		],
		[
			"code"   => "IM",
			"label"  => "Intermediate Mid",
			"weight" => 5
		],
		[
			"code"   => "IL",
			"label"  => "Intermediate Low",
			"weight" => 4
		],
		[
			"code"   => "NH",
			"label"  => "Novice High",
			"weight" => 3
		],
		[
			"code"   => "NM",
			"label"  => "Novice Mid",
			"weight" => 2
		],
		[
			"code"   => "NL",
			"label"  => "Novice Low",
			"weight" => 1
		]
	];

	if(!Database::tableExists('grad_requirements_options')) {
		$text_type = Database::$type === 'mssql' ? 'VARCHAR(MAX)' : 'TEXT';

		Database::query("
			CREATE TABLE grad_requirements_options (
				id BIGINT PRIMARY KEY,
				requirement_id BIGINT NULL,
				requirement_category_id BIGINT NULL,
				min_syear BIGINT NULL,
				max_syear BIGINT NULL,
				code VARCHAR(255) NOT NULL,
				title {$text_type} NULL,
				weight NUMERIC NOT NULL
			)
		");
	}

	if(!Database::sequenceExists('grad_requirements_options_seq')) {
		Database::createSequence('grad_requirements_options_seq');
	}

	$requirement_category = GradRequirementsCategory::getOne([
		"CLASS_NAME = 'BiliteracySealCategoryGradRequirementsReport'"
	]);

	if(empty($requirement_category)) {
		throw new Error("Biliteracy Seal was not set up correctly");
	}

	$requirement_category_id = intval($requirement_category->getId());
	$new_options             = [];

	foreach($fields_options as $option) {
		$where = [
			"requirement_category_id = :category_id",
			"code = :code"
		];

		$params = [
			'category_id' => $requirement_category_id,
			'code'        => $option['code']
		];

		$existing = GradRequirementsOption::getOne($where, null, $params);

		if(empty($existing)) {
			$new_option = new GradRequirementsOption();

			$new_option
				->setCode($option['code'])
				->setTitle($option['label'])
				->setWeight($option['weight'])
				->setRequirementCategoryId($requirement_category_id);

			$new_options[] = $new_option;
		}
	}

	GradRequirementsOption::insert($new_options);

	$all_fields = [];

	$option_query = "
		SELECT
			o.id,
			code,
			o.title AS label,
			(
				CASE WHEN
					COALESCE({syear}, 0) BETWEEN COALESCE(min_syear, -1) AND COALESCE(max_syear, 9999)
				THEN 0
				ELSE 1
				END
			) AS inactive,
			ROW_NUMBER() OVER (ORDER BY o.weight DESC) AS sort_order
		FROM
			grad_requirements_options o JOIN
			grad_requirements_category c ON
				c.id = o.requirement_category_id
		WHERE
			c.class_name = 'BiliteracySealCategoryGradRequirementsReport'
	";

	$category = CustomFieldCategory::getOne("title = 'Graduation'");
	$category_id = null;

	if($category){
		$category_id = $category->getId();
	}

	if(!empty($category_id)){
		$where = [
			"category_id = :category_id"
		];

		$params = [
			'category_id' => $category_id
		];

		$last_sort_order = CustomFieldJoinCategory::getOne($where, 'SORT_ORDER DESC', $params);
		$last_sort_order = $last_sort_order->getSortOrder();
	}

	foreach($fields as $field) {

		$tmp_field = SISStudent::getFieldByColumnName($field['column_name']);

		if(empty($tmp_field)) {
			$obj = new CustomField();

			$tmp_field = $obj
					->setSourceClass('SISStudent')
					->setRecord($field)
					->setOptionQuery($option_query)
					->setSystem(1)
					->setAlias($field['alias'])
					->setDescription("This is used to calculate Gold or Silver status for the Biliteracy Seal on the Graduation Requirements Report.")
					->persist();

			$field_id              = $tmp_field->getId();
			$all_fields[$field_id] = $tmp_field;

			if(!empty($category_id)){
				$where = [
					"category_id = :category_id",
					"field_id = :field_id"
				];

				$params = [
					'category_id' => $category_id,
					'field_id'    => $field_id
				];

				$existing = CustomFieldJoinCategory::getOne($where, null, $params);

				if(!$existing){
					$custom_field_join_category = new CustomFieldJoinCategory();

					$custom_field_join_category
						->setCategoryId($category_id)
						->setFieldId($field_id)
						->setSortOrder(++$last_sort_order)
						->persist();
				}
			}
		}
	}
	Database::commit();

	SISStudent::refreshViews();