<?php
//Remove Merit and Scholar requirements for texas

// $error_reporting = error_reporting(E_ALL);
// $display_errors  = ini_set('display_errors', 1);
// $GLOBALS['disable_login_redirect'] = true;
// require_once('../Warehouse.php');

$state_name = null;
if(!empty($GLOBALS['_FOCUS']['config']['state_name'])) {
	$state_name = strtolower($GLOBALS['_FOCUS']['config']['state_name']);
}

if($state_name === 'florida'){
	return;
}

Database::begin();

	if (Database::tableExists('grad_requirements_category')) {

		$merit_requirement_category = GradRequirementsCategory::getOne([
			"CLASS_NAME = 'MeritCategoryGradRequirementsReport'"
		]);


		if(!empty($merit_requirement_category)) {
			$category_id = $merit_requirement_category->getId();

			$sql = "DELETE FROM GRAD_REQUIREMENTS WHERE grad_requirements_category_id = {$category_id}";
			Database::query($sql);

			$sql = "DELETE FROM GRAD_REQUIREMENTS_CATEGORY WHERE id = {$category_id}";
			Database::query($sql);
		}

		$scholar_requirement_category = GradRequirementsCategory::getOne([
			"CLASS_NAME = 'ScholarCategoryGradRequirementsReport'"
		]);


		if(!empty($scholar_requirement_category)) {
			$category_id = $scholar_requirement_category->getId();

			$sql = "DELETE FROM GRAD_REQUIREMENTS WHERE grad_requirements_category_id = {$category_id}";
			Database::query($sql);

			$sql = "DELETE FROM GRAD_REQUIREMENTS_CATEGORY WHERE id = {$category_id}";
			Database::query($sql);
		}
	}

Database::commit();