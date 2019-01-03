<?php
$GLOBALS['disable_login_redirect'] = true;
$running_as_cron                   = true;
$runningCron                       = true;

require_once __DIR__ . '/../Warehouse.php';

//error_reporting(-1);
//ini_set('display_errors', 'On');
if (!empty($_REQUEST['pop_years'])) {
	$year_clause = "AND syear IN (".implode(',',$_REQUEST['pop_years']).")";
} else {
	$year_clause = "";
}
if (!empty($_REQUEST['pop_schools'])) {
	$school_clause = "AND school_id IN (".implode(',',$_REQUEST['pop_schools']).")";
} else {
	$school_clause = "";
}
set_time_limit(0);
$students = Database::get(
	"SELECT DISTINCT STUDENT_ID,SCHOOL_ID,SYEAR
	FROM STUDENT_ENROLLMENT
	WHERE
		GRADE_ID IN (SELECT ID FROM SCHOOL_GRADELEVELS WHERE SHORT_NAME IN ('09','10','11','12'))
		AND COALESCE(CUSTOM_9, 'N') != 'Y'
		AND END_DATE IS NULL
		AND GRADUATION_REQUIREMENT_PROGRAM IS NOT NULL
		{$year_clause}
		{$school_clause}
	ORDER BY SYEAR ASC, SCHOOL_ID ASC"
);
$students = Database::reindex($students, ['SYEAR', 'SCHOOL_ID']);

$quarters = Database::get(
	"SELECT MARKING_PERIOD_ID, SCHOOL_ID, SYEAR
	FROM SCHOOL_QUARTERS
	WHERE
		START_DATE < '".DBDate()."'
		{$year_clause}
		{$school_clause}
	ORDER BY START_DATE DESC"
);
$quarters = Database::reindex($quarters, ['SCHOOL_ID', 'SYEAR']);

$school_titles = Database::get(
	"SELECT ID, TITLE FROM SCHOOLS"
);
$school_titles = Database::reindex($school_titles, ['ID']);

$_REQUEST['popSummary'] = 'Y';
$_REQUEST['mp']='COURSE_HISTORY';
$modname = 'utilities/PopSummary';
echo "<br>Populating...";
foreach($students as $year => $schools) {
	$_SESSION['UserSyear'] = $year;
	echo "<br>{$year}: ";
	foreach ($schools as $school => $students) {
		echo "<br>{$school_titles[$school][0]['TITLE']}: ";
		echo count($students)." students";
		$_SESSION['UserMP'] = $quarters[$school][$year][0]['MARKING_PERIOD_ID'];
		$_SESSION['UserSchool'] = $school;

		foreach ($students as $student) {
			Database::begin();

			unset($_REQUEST['scale_id']);
			if ($_REQUEST['force_recalc'] == 'Y') {
				Database::query("DELETE FROM GRAD_REQUIREMENTS_SUMMARY WHERE STUDENT_ID = {$student['STUDENT_ID']}");
			}
			$_SESSION['student_id'] = $student['STUDENT_ID'];

			if ($_SESSION['student_id']) {
				$scale_id = Database::get("
					SELECT
						graduation_requirement_program
					FROM
						student_enrollment
					WHERE
						syear = :syear
						AND school_id = :school_id
						AND student_id = :student_id
					ORDER BY
						start_date DESC
				", [
					'syear'      => $_SESSION['UserSyear'],
					'school_id'  => $_SESSION['UserSchool'],
					'student_id' => $_SESSION['student_id']
				]);

				$_REQUEST['scale_id'] = $scale_id[0]['GRADUATION_REQUIREMENT_PROGRAM'];
			}

			if (empty($_REQUEST['scale_id'])) {
				$scale_id = Database::get("
					SELECT
						id
					FROM
						grad_subject_programs
					WHERE
						school_id='{$_SESSION['UserSchool']}'
					ORDER BY
						COALESCE(default_category,'N') DESC
				");
				$_REQUEST['scale_id'] = $scale_id[0]['ID'];
			}

			if (empty($_REQUEST['scale_id'])) {
				exit('<br><b>No Graduation Requirements Exist for the Current School.</b>');
			}

			$_REQUEST['effective_date'] = date('Y-m-d');

			$mp_terms = array('SCHOOL_YEARS'=>_('Full-Year Grades'),'SCHOOL_SEMESTERS'=>_('Semester Grades'),'SCHOOL_QUARTERS'=>_('Quarter Grades'),'COURSE_HISTORY'=>_('Course History'));

			if (empty($_REQUEST['mp'])) {
				if (SystemPreferences('WEIGHT_GPA_BY_CREDITS')!='Y') {
					$_REQUEST['mp'] = 'SCHOOL_YEARS';
				} else {
					$_REQUEST['mp'] = 'COURSE_HISTORY';
				}
			}

			if (UserMP()) {
				$sem = GetParentMP('SEM',UserMP());
				if (!GetMP($sem,'START_DATE')) {
					unset($mp_terms['SCHOOL_SEMESTERS']);
				} elseif(!$_REQUEST['mp']) {
					$_REQUEST['mp'] = 'SCHOOL_SEMESTERS';
				}

				$fy = GetParentMP('FY',$sem);
				if (!GetMP($fy,'START_DATE')) {
					unset($mp_terms['SCHOOL_YEARS']);
				} elseif(!$_REQUEST['mp']) {
					$_REQUEST['mp'] = 'SCHOOL_YEARS';
				}

				if (!GetMP(UserMP(),'START_DATE')) {
					unset($mp_terms['SCHOOL_QUARTERS']);
				} elseif(!$_REQUEST['mp']) {
					$_REQUEST['mp'] = 'SCHOOL_QUARTERS';
				}
			} else {
				$_REQUEST['mp'] = 'SCHOOL_SEMESTERS';
			}

			$grad_subject_programs_id = $_REQUEST['scale_id'];
			$params = [
				[
					[
						'CLASS_NAME' => 'MainCategoryGraduationRequirementsReport'
					]
				],
				$_SESSION['student_id'],
				$_REQUEST['mp'],
				$_REQUEST['effective_date'],
				$grad_subject_programs_id
			];

			$grad_req_data = GraduationRequirementsHelper::getOneStudentReportData($params);

			$records = [];
			foreach ($grad_req_data['MainCategoryGraduationRequirementsReport']['_GRADUATION_SUBJECTS'] as $grad_subj_key => $grad_subj_value) {

				$records[] = [
					'calculated_date'     => DBDate(),
					'earned'              => $grad_subj_value['_CURRENTLY_COMPLETED_CREDITS'],
					'grad_program_id'     => $grad_subj_value['GRAD_PROGRAM_ID'],
					'grad_req_short_name' => $grad_subj_value['SHORT_NAME'],
					'required'            => $grad_subj_value['_CREDITS_REQUIRED'],
					'student_id'          => $_SESSION['student_id'],
					'syear'               => $_SESSION['UserSyear']
				];
			}

			$columns = [
				'calculated_date',
				'earned',
				'grad_program_id',
				'grad_req_short_name',
				'required',
				'student_id',
				'syear'
			];

			Database::insert('grad_requirements_summary', null, $columns, $records);

			Database::commit();
		}
	}
}