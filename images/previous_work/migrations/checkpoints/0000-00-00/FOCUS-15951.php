<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"])
	return false;

if (!Database::columnExists('gl_hr_pd_roster', 'subject_area_id'))
	Database::createColumn('gl_hr_pd_roster', 'subject_area_id', 'bigint');

if (!Database::columnExists('gl_hr_pd_roster', 'bank_used'))
	Database::createColumn('gl_hr_pd_roster', 'bank_used', 'bigint');

if (!Database::columnExists('gl_hr_pd_roster', 'banked_year'))
	Database::createColumn('gl_hr_pd_roster', 'banked_year', 'bigint');

Database::begin();

$perms = [ "can_view", "can_edit", "can_create", "can_delete" ];
$enc1  = (Database::$type == "postgres" ? "\"" : "[");
$enc2  = (Database::$type == "postgres" ? "\"" : "]");
$cfx   = [
	"prof_dev"            => [
		"title" => "Professional Development",
		"query" => "
			SELECT    pdr.staff_id,
			          CASE
			             WHEN pdc.district = '01' THEN '01 Alachua'
			             WHEN pdc.district = '02' THEN '02 Baker'
			             WHEN pdc.district = '03' THEN '03 Bay'
			             WHEN pdc.district = '04' THEN '04 Bradford'
			             WHEN pdc.district = '05' THEN '05 Brevard'
			             WHEN pdc.district = '06' THEN '06 Broward'
			             WHEN pdc.district = '07' THEN '07 Calhoun'
			             WHEN pdc.district = '08' THEN '08 Charlotte'
			             WHEN pdc.district = '09' THEN '09 Citrus'
			             WHEN pdc.district = '10' THEN '10 Clay'
			             WHEN pdc.district = '11' THEN '11 Collier'
			             WHEN pdc.district = '12' THEN '12 Columbia'
			             WHEN pdc.district = '13' THEN '13 Miami-Dade'
			             WHEN pdc.district = '14' THEN '14 DeSoto'
			             WHEN pdc.district = '15' THEN '15 Dixie'
			             WHEN pdc.district = '16' THEN '16 Duval'
			             WHEN pdc.district = '17' THEN '17 Escambia'
			             WHEN pdc.district = '18' THEN '18 Flagler'
			             WHEN pdc.district = '19' THEN '19 Franklin'
			             WHEN pdc.district = '20' THEN '20 Gadsden'
			             WHEN pdc.district = '21' THEN '21 Gilchrist'
			             WHEN pdc.district = '22' THEN '22 Glades'
			             WHEN pdc.district = '23' THEN '23 Gulf'
			             WHEN pdc.district = '24' THEN '24 Hamilton'
			             WHEN pdc.district = '25' THEN '25 Hardee'
			             WHEN pdc.district = '26' THEN '26 Hendry'
			             WHEN pdc.district = '27' THEN '27 Hernando'
			             WHEN pdc.district = '28' THEN '28 Highlands'
			             WHEN pdc.district = '29' THEN '29 Hillsborough'
			             WHEN pdc.district = '30' THEN '30 Holmes'
			             WHEN pdc.district = '31' THEN '31 Indian River'
			             WHEN pdc.district = '32' THEN '32 Jackson'
			             WHEN pdc.district = '33' THEN '33 Jefferson'
			             WHEN pdc.district = '34' THEN '34 Lafayette'
			             WHEN pdc.district = '35' THEN '35 Lake'
			             WHEN pdc.district = '36' THEN '36 Lee'
			             WHEN pdc.district = '37' THEN '37 Leon'
			             WHEN pdc.district = '38' THEN '38 Levy'
			             WHEN pdc.district = '39' THEN '39 Liberty'
			             WHEN pdc.district = '40' THEN '40 Madison'
			             WHEN pdc.district = '41' THEN '41 Manatee'
			             WHEN pdc.district = '42' THEN '42 Marion'
			             WHEN pdc.district = '43' THEN '43 Martin'
			             WHEN pdc.district = '44' THEN '44 Monroe'
			             WHEN pdc.district = '45' THEN '45 Nassau'
			             WHEN pdc.district = '46' THEN '46 Okaloosa'
			             WHEN pdc.district = '47' THEN '47 Okeechobee'
			             WHEN pdc.district = '48' THEN '48 Orange'
			             WHEN pdc.district = '49' THEN '49 Osceola'
			             WHEN pdc.district = '50' THEN '50 Palm Beach'
			             WHEN pdc.district = '51' THEN '51 Pasco'
			             WHEN pdc.district = '52' THEN '52 Pinellas'
			             WHEN pdc.district = '53' THEN '53 Polk'
			             WHEN pdc.district = '54' THEN '54 Putnam'
			             WHEN pdc.district = '55' THEN '55 St. Johns'
			             WHEN pdc.district = '56' THEN '56 St. Lucie'
			             WHEN pdc.district = '57' THEN '57 Santa Rosa'
			             WHEN pdc.district = '58' THEN '58 Sarasota'
			             WHEN pdc.district = '59' THEN '59 Seminole'
			             WHEN pdc.district = '60' THEN '60 Sumter'
			             WHEN pdc.district = '61' THEN '61 Suwannee'
			             WHEN pdc.district = '62' THEN '62 Taylor'
			             WHEN pdc.district = '63' THEN '63 Union'
			             WHEN pdc.district = '64' THEN '64 Volusia'
			             WHEN pdc.district = '65' THEN '65 Wakulla'
			             WHEN pdc.district = '66' THEN '66 Walton'
			             WHEN pdc.district = '67' THEN '67 Washington'
			             WHEN pdc.district = '68' THEN '68 Florida School for Deaf/Blind'
			             WHEN pdc.district = '69' THEN '69 Washington Special'
			             WHEN pdc.district = '71' THEN '71 Florida Virtual School'
			             WHEN pdc.district = '72' THEN '72 FAU - Lab School'
			             WHEN pdc.district = '73' THEN '73 FSU - Lab School'
			             WHEN pdc.district = '74' THEN '74 FAMU - Lab School'
			             WHEN pdc.district = '75' THEN '75 UF - Lab School'
			             WHEN pdc.district = '70' THEN '70 Eckerd Challenge Program (prior to 1994)'
			             WHEN pdc.district = '76' THEN '76 Department of Corrections'
			             WHEN pdc.district = '77' THEN '77 Community Colleges'
			             WHEN pdc.district = '78' THEN '78 Florida Connections Academy (valid from 2004-05 through 2008-09)'
			             WHEN pdc.district = '79' THEN '79 Florida Virtual Academy (valid from 2004-05 through 2008-09)'
			             WHEN pdc.district = '88' THEN '88 Division of Public Schools (Academic Scholar Records)'
			             WHEN pdc.district = '99' THEN '99 Other than a Florida Public School'
			          END AS \"District\",
			          RTRIM(LTRIM(CONCAT(COALESCE(pdc.component, ' '), ' ', COALESCE(pdc.title, ' ')))) AS \"Component\",
			          CONCAT(pda.section, ' ', pda.title) AS \"Activity\",
			          CAST(pda.start_date AS DATE) AS \"Start\",
			          CAST(pda.end_date AS DATE) AS \"End\",
			          cfso.label AS \"Subject Area\",
			          CASE
			             WHEN purpose = 'A' THEN 'A Add-on Endorsement'
			             WHEN purpose = 'B' THEN 'B Alternative Certification'
			             WHEN purpose = 'C' THEN 'C FL Educators Cert Renewal'
			             WHEN purpose = 'D' THEN 'D Other Cert/Lic Renewal'
			             WHEN purpose = 'E' THEN 'E Professional Skill Building'
			             WHEN purpose = 'F' THEN 'F W Cecil Golden Program'
			             WHEN purpose = 'G' THEN 'G District Leadership Dev'
			             WHEN purpose = 'H' THEN 'H No Certification'
			             WHEN purpose = 'N' THEN 'N No Purpose (District)'
			             WHEN purpose = 'Y' THEN 'Y Other Purpose (District)'
			             ELSE NULL
			          END AS \"Purpose\",
			          pdr.points as \"Certification Points\",
			          pdr.noncert_points AS \"Non-Certification Points\",
			          pdr.banked_year AS \"Banked Year\",
			          CASE
			             WHEN pdr.bank_used = '1' THEN 'Yes'
			             ELSE 'No'
			          END AS \"Bank Used\",
			          CONCAT(
			             '<a href=\"Modules.php?force_package=Finance&modname=hr_professional_development&rosDistrict=',
			             pdc.district,
			             '&rosComponent=',
			             pda.component_id,
			             '&rosActivity=',
			             pdr.activity_id,
			             '#!divRosters\" target=\"_blank\">Edit</a>'
			          ) AS \"Edit\"
			FROM      gl_hr_pd_roster pdr
			JOIN      gl_hr_pd_activity pda ON pda.id = pdr.activity_id
			JOIN      gl_hr_pd_component pdc ON pdc.id = pda.component_id
			LEFT JOIN custom_field_select_options cfso ON cfso.id = pdr.subject_area_id"
	],
	"subject_area_points" => [
		"title" => "Subject Area Points Summary",
		"query" => "
			SELECT   pdr.staff_id,
			         cfso.code AS \"Subject\",
			         cfso.label AS \"Description\",
			         SUM(
			            CASE
			               WHEN
			                  pdr.bank_used = 1 AND
			                  pdr.banked_year IS NOT NULL AND
			                  (
			                     CAST(CONCAT(CAST(pdr.banked_year + 1 AS VARCHAR), '-06-30') AS DATE) >= CAST(COALESCE(cfle.log_field4, '1970-01-01') AS DATE) OR
			                     (
			                        CAST(COALESCE(pda.start_date, '1970-01-01') AS DATE) <= CAST(COALESCE(cfle.log_field5, '2099-12-31') AS DATE) AND
			                        CAST(COALESCE(pda.end_date, '2099-12-31') AS DATE) >= CAST(COALESCE(cfle.log_field4, '1970-01-01') AS DATE)
			                     )
			                  )
			                  THEN pdr.points
			               WHEN
			                  pdr.banked_year IS NULL AND
			                  CAST(COALESCE(pda.start_date, '1970-01-01') AS DATE) <= CAST(COALESCE(cfle.log_field5, '2099-12-31') AS DATE) AND
			                  CAST(COALESCE(pda.end_date, '2099-12-31') AS DATE) >= CAST(COALESCE(cfle.log_field4, '1970-01-01') AS DATE)
			                  THEN pdr.points
			               ELSE 0
			            END
			         ) AS \"Points\",
			         SUM(
			            CASE
			               WHEN
			                  COALESCE(pdr.bank_used, 0) != 1 AND
			                  pdr.banked_year IS NOT NULL AND
			                  (
			                     CAST(CONCAT(CAST(pdr.banked_year + 1 AS VARCHAR), '-06-30') AS DATE) >= CAST(COALESCE(cfle.log_field4, '1970-01-01') AS DATE) OR
			                     (
			                        CAST(COALESCE(pda.start_date, '1970-01-01') AS DATE) <= CAST(COALESCE(cfle.log_field5, '2099-12-31') AS DATE) AND
			                        CAST(COALESCE(pda.end_date, '2099-12-31') AS DATE) >= CAST(COALESCE(cfle.log_field4, '1970-01-01') AS DATE)
			                     )
			                  )
			                  THEN pdr.points
			               ELSE 0
			            END
			         ) AS \"Banked Points\"
			FROM     custom_field_select_options cfso
			JOIN     custom_field_log_entries cfle ON cfle.log_field1 = CAST(cfso.id AS VARCHAR)
			JOIN     gl_hr_pd_roster pdr ON pdr.subject_area_id = cfso.id AND pdr.staff_id = cfle.source_id
			JOIN     gl_hr_pd_activity pda ON pda.id = pdr.activity_id
			WHERE    cfle.source_class = 'FocusUser'
			GROUP BY pdr.staff_id, cfso.code, cfso.label"
	]
];
$cfc   = CustomFieldCategory::getOne("source_class = 'FocusUser' AND title = 'Teachers'");

if (!$cfc)
	$cfc = (new CustomFieldCategory())
		->setTitle("Teachers")
		->setSourceClass("FocusUser")
		->setSortOrder(2)
		->setErp(1)
		->setSis(1)
		->setDefaultProfilesView(["1"])
		->setDefaultProfilesEdit(["1"])
		->persist();

$cfcID = $cfc->getID();

foreach ($cfx as $k => $v)
{
	$cf = CustomField::getOne("source_class = 'FocusUser' AND alias = '{$k}'");

	if (!$cf)
		$cf = (new CustomField())->setSourceClass("FocusUser")->setType("computed_table")->setAlias($k)->setRequiresAuthentication(1);

	$cf->setTitle($v["title"])->setComputedQuery($v["query"])->persist();

	$cfID = $cf->getID();

	foreach ($perms as $perm)
	{
		$tmp = Permission::getOne("profile_id = 1 AND {$enc1}key{$enc2} = 'FocusUser:{$cfID}:{$perm}'");

		if (!tmp)
			$tmp = (new Permission())->setProfileId(1)->setKey("FocusUser:{$cfID}:{$perm}")->persist();
	}

	$cfjc = CustomFieldJoinCategory::getOne("field_id = {$cfID} AND category_id = {$cfcID}");

	if (!$cfjc)
		$cfjc = (new CustomFieldJoinCategory())->setFieldId($cfID)->setCategoryId($cfcID)->setSortOrder(0)->persist();
}

Database::commit();

return true;
