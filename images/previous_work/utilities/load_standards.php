<?php

require_once('../Warehouse.php');

//enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

//no memory limit
set_time_limit(0);
ini_set("memory_limit", "-1");

$s_year = UserSyear();

$options = [
	"ssl" => [
		"verify_peer"      => false,
		"verify_peer_name" => false,
	]
];
$context = stream_context_create($options);

if(!empty($_FILES) && !empty($_FILES['standards']['tmp_name']))
{
	$s_year = $_POST['syear'];
	$str = file_get_contents($_FILES['standards']['tmp_name'], false, $context);
}
else if(!empty($_REQUEST['standardsURL']))
{
	$s_year = $_REQUEST['syear'];
	$str = file_get_contents($_REQUEST['standardsURL'], false, $context);
}
else
{
	?>

<!DOCTYPE html>
<html>
<head>
	<title>Upload Standards File</title>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
</head>
<body style="margin-top:20px;">
	<div class="container">
		<div class="well">
			<h3 style="margin-top:0;">Upload Standards File</h3>
			<form class="form-horizontal" enctype="multipart/form-data" method="POST">
				<div class="form-group form-group-sm">
					<label class="col-sm-1 control-label" for="syear">syear:</label>
					<div class="col-sm-1">
						<input type="number" class="form-control" name="syear" style="min-width: 65px;" value="<?= $s_year ?>">
					</div>
				</div>
				<div class="form-group form-group-sm">
					<label for="standardsURL" class="col-sm-1 control-label">remote url:</label>
					<div class="col-sm-5">
						<input type="url" class="form-control" name="standardsURL">
					</div>
					<span class="col-sm-1 form-control-static text-center">
						<b>OR</b>
					</span>
					<div class="col-sm-5">
						<input type="file" class="form-control" name="standards" style="height: initial;">
					</div>
				</div>
				<button type="submit" class="btn btn-default">Upload</button>
			</form>
		</div>
	</div>
</body>
</html>

	<?php
	die(0);
}

$dateTime = DateTime::createFromFormat('Y', $s_year);
if($dateTime === false)
{
	die("invalid syear");
}

//if the variables failed to get set, kill this program
$standards = json_decode($str, true);
if(empty($str) || empty($standards)) {
	die('error reading standards file');
}

Database::begin();

//clear tables to make way for $s_year CTE standards
Database::query("DELETE FROM standards_join_courses WHERE standard_id IN (SELECT id FROM standards WHERE cte_import = '1' AND syear = {$s_year});");
Database::query("DELETE FROM standards WHERE cte_import = '1' AND syear = {$s_year};");
Database::query("DELETE FROM standard_categories_1 WHERE cte_import = '1' AND syear = {$s_year};");
Database::query("DELETE FROM standard_categories_2 WHERE cte_import = '1' AND syear = {$s_year};");

$cat1_id = null;
$cat2_id = null;

//sort numbers for each standard category table (descends)
$sort1_num          = 240;
$sort2_num          = 5858;
$standard_sort_num  = -40554;

//nested foreach loops to grabs & sets all course information
foreach($standards as $i => $curriculums) {
	foreach($curriculums['categories'] as $j => $category) {
		foreach($category['courses'] as $k => $courses) {

			//make sure course is a PSAV program
			if (strpos($category['category'], 'PSAV') !== false ||
				strpos($category['category'], 'Programs Daggered for Deletion') !== false) {

				//If course number is unknown, set it to null
				if (strpos($courses['num'], 'Unknown') !== false) {
					$courses['num'] = null;
				}

				if (strpos($courses['num'], 'PSAV') !== false) {
					$courses['num'] = str_replace('PSAV - ', '', $courses['num']);
				}

				if (strlen($courses['num']) > 10) {
					continue;
				}

				//populate standard_categories_1 table
				$inserted = Database::get(Database::preprocess("
					INSERT INTO standard_categories_1 (
						ID,
						TITLE,
						SHORT_NAME,
						SORT_ORDER,
						SYEAR,
						CTE_IMPORT
					)
					{{mssql:OUTPUT inserted.ID}}
					VALUES (
						{{next:standard_categories_1_seq}},
						'{$courses['num']} - {$courses['title']}',
						'{$courses['num']}',
						{$sort1_num},
						{$s_year},
						'1'
					)
					{{postgres:RETURNING ID}}
				"));
				$cat1_id = $inserted[0]['ID'];

				//decrease category 1 sort number
				$sort1_num--;


				//put course properties & course standards into their own array variables
				$properties     = isset($courses['properties']) ? $courses['properties'] : null;
				$meta_standards = isset($courses['standards']) ? $courses['standards'] : null;

			}

			//loop through standards for this specific course
			if(isset($meta_standards)) {
				foreach ($meta_standards as $m => $temp_standards) {
					foreach ($temp_standards['standards'] as $t => $individual_standards) {

						//make sure course is a PSAV program
						if (strpos($category['category'], 'PSAV') !== false ||
							strpos($category['category'], 'Programs Daggered for Deletion') !== false) {

							//get rid of single apostrophe's in standards titles (messes up queries)
							if (strpos($individual_standards['title'], "'") !== false) {
								$individual_standards['title'] = str_replace("'", "", $individual_standards['title']);
							}

							//populate the standard_categories_2 table
							if (strlen(substr(strchr($individual_standards['id'], "."), 1)) == 1) {
								$inserted = Database::get(Database::preprocess("
									INSERT INTO standard_categories_2 (
										ID,
										TITLE,
										SORT_ORDER,
										SYEAR,
										PARENT_ID,
										CTE_IMPORT
									)
									{{mssql:OUTPUT inserted.ID}}
									VALUES (
										{{next:standard_categories_2_seq}},
										'{$individual_standards['id']}: {$individual_standards['title']}',
										{$sort2_num},
										{$s_year},
										{$cat1_id},
										'1'
									)
									{{postgres:RETURNING ID}}
								"));
								$cat2_id = $inserted[0]['ID'];

								//decrease category 2 sort num
								$sort2_num--;

							}

							//insert into standards; table
							if (strlen(substr(strchr($individual_standards['id'], "."), 1)) != 1) {
								$inserted = Database::get(Database::preprocess("
									INSERT INTO standards (
										ID,
										SORT_ORDER,
										TITLE,
										DESCRIPTION,
										SYEAR,
										CATEGORY_1_ID,
										CATEGORY_2_ID,
										CTE_IMPORT
									)
									{{mssql:OUTPUT inserted.ID}}
									VALUES (
										{{next:standards_seq}},
										{$standard_sort_num},
										'{$individual_standards['id']}: {$individual_standards['title']}',
										'{$individual_standards['id']}: {$individual_standards['title']}',
										{$s_year},
										{$cat1_id},
										{$cat2_id},
										'1'
									)
									{{postgres:RETURNING ID}}
								"));
								$standard_id = $inserted[0]['ID'];

								//grab course number and make sure it's a valid input
								$link_courses = !empty($temp_standards['tags'][0]['value']) ? $temp_standards['tags'][0]['value'] : $courses['num'];
								//make sure it's not too long
								if (strlen($link_courses) > 24){
									$link_courses = $courses['num'];
								}
								//update standard_join_courses table
								Database::query("INSERT into standards_join_courses ( 
										COURSE_NUM,
										STANDARD_ID
									) VALUES (
										'{$link_courses}%',
										{$standard_id}
									)"
								);

								//increase standard sort num
								$standard_sort_num++;

							}

						}


					}
				}
			}


		}
	}
}

Database::commit();

echo "complete";
