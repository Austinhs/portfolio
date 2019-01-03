<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

$cfc = CustomFieldCategory::getOne("source_class = 'FocusUser' AND title = 'Personnel Evaluation'");

if (!$cfc)
	$cfc = (new CustomFieldCategory())
		->setTitle("Personnel Evaluation")
		->setSourceClass("FocusUser")
		->setSortOrder(0)
		->setErp(1)
		->setDefaultProfilesView(["1"])
		->setDefaultProfilesEdit(["1"])
		->persist();

$cf = CustomField::getOne("source_class = 'FocusUser' AND alias = 'pers_eval'");

if (!$cf)
	$cf = (new CustomField())
		->setSourceClass("FocusUser")
		->setType("log")
		->setTitle("Personnel Evaluation")
		->setAlias("pers_eval")
		->setSystem(1)
		->setRequiresAuthentication(1)
		->persist();

$perms = [ "can_view", "can_edit", "can_create", "can_delete" ];
$cf_id = $cf->getID();

foreach ($perms as $perm)
{
	if (Database::$type == "postgres")
		$tmp = Permission::getOne("profile_id = 1 AND \"key\" = 'FocusUser:{$cf_id}:{$perm}'");
	else
		$tmp = Permission::getOne("profile_id = 1 AND [key] = 'FocusUser:{$cf_id}:{$perm}'");

	if (!tmp)
		$tmp = (new Permission())
			->setProfileId(1)
			->setKey("FocusUser:{$cf_id}:{$perm}")
			->persist();
}

$cfjc = CustomFieldJoinCategory::getOne("field_id = " . $cf->getID() . " AND category_id = " . $cfc->getID());

if (!$cfjc)
	$cfjc = (new CustomFieldJoinCategory())
		->setFieldId($cf->getID())
		->setCategoryId($cfc->getID())
		->setSortOrder(0)
		->persist();

$logFields = [
	"1" => "Fiscal Year",
	"2" => "Final Evaluation",
	"3" => "Evaluation Rating",
];

foreach ($logFields as $col => $lf)
{
	$cflc = CustomFieldLogColumn::getOne("field_id = " . $cf->getID() . " AND column_name = 'LOG_FIELD{$col}'");

	if (!$cflc)
		$cflc = (new CustomFieldLogColumn())
			->setFieldId($cf->getID())
			->setColumnName("LOG_FIELD{$col}")
			->setType($col == "1" ? "text" : "select")
			->setTitle($lf)
			->setSortOrder($col)
			->persist();

	switch ($col)
	{
		case "1" : $options = []; break;
		case "2" : $options = [ "Y" => "Full Year", "N" => "Mid Year" ]; break;
		case "3" :
			$options = [
				"E" => "Needs Improvement",
				"I" => "Not Evaluated",
				"C" => "Highly Effective",
				"D" => "Effective",
				"G" => "Unsatisfactory",
				"O" => "Good",
				"M" => "Commendable"
			];
		break;
	}

	foreach ($options as $k => $v)
	{
		$cfso = CustomFieldSelectOption::getOne("source_class = 'CustomFieldLogColumn' AND source_id = " . $cflc->getID() . " AND code = '{$k}'");

		if (!$cfso)
			$cfso = (new CustomFieldSelectOption())
				->setSourceClass("CustomFieldLogColumn")
				->setSourceId($cflc->getID())
				->setCode($k);

		$cfso->setLabel($v)->persist();
	}

	$perms   = [ "can_view", "can_edit" ];
	$cflc_id = $cflc->getID();

	foreach ($perms as $perm)
	{
		if (Database::$type == "postgres")
			$tmp = Permission::getOne("profile_id = 1 AND \"key\" = 'FocusUser:{$cf_id}#{$cflc_id}:{$perm}'");
		else
			$tmp = Permission::getOne("profile_id = 1 AND [key] = 'FocusUser:{$cf_id}#{$cflc_id}:{$perm}'");

		if (!tmp)
			$tmp = (new Permission())
				->setProfileId(1)
				->setKey("FocusUser:{$cf_id}#{$cflc_id}:{$perm}")
				->persist();
	}
}

Database::commit();

return true;
