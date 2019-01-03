<?php

$arg_options = getopt(':', ['recalc-dt-grades-job::']);
$is_cli      = in_array(php_sapi_name(), [ 'cli', 'cgi', 'cgi-fcgi' ]);
$is_job      = $is_cli && !empty($arg_options['recalc-dt-grades-job']);

if ($is_job) {
	$GLOBALS['disable_login_redirect'] = true;
}

require_once __DIR__ . '/../Warehouse.php';
require_once $staticpath . 'ProgramFunctions/_makeLetterGrade.fnc.php';
require_once $staticpath . 'modules/Grades/Components/GUser.php';
require_once $staticpath . 'classes/Updater/SVN.class.php';

ini_set('display_errors', true);
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING ^ E_STRICT);
set_time_limit(0);

if (!$is_cli && User('PROFILE') != 'admin') {
	echo 'Must be logged in as admin';
	exit();
}
global $memory_threshold, $display_interval;

$display_interval = 10;
$memory_threshold = 1024 * 1024 * 750; //Start clearing caches after 750 MB delta has been reached
$sections_per_job = 200;

if (!empty($GLOBALS['RECALC_DT_GRADES_SECTIONS_PER_CHUNK'])) {
	$sections_per_job = $GLOBALS['RECALC_DT_GRADES_SECTIONS_PER_CHUNK'];
}

//Some profiling
global $cd, $md;
$md = new MemoryDelta('Recalc DT Grades');
$cd = CompositeDelta::create($md, new TimeDelta('Recalc DT Grades'));
$cd->subscribe('GetCurrentGrade');

if(!function_exists('GetMPSelectionHTML')) {
	//Renders a logical/absolute selection into a nicely formatted monospaced graph
	function GetMPSelectionHTML($MPCollection) {
		$html = "<pre style=\"text-align: left\">\n";
		foreach (new MPIterator($MPCollection, MPIterator::MODE_PRESERVE_ORDER, true) as $parent_mp => $item) {
			$src_subquery = MPExplorer::$src_subquery;
			if ($item['type'] != 'schools')
				$ret = DBGet(DBQuery("{$src_subquery} WHERE ID={$item['id']}"));
			else
				$ret = DBGet(DBQuery("SELECT TITLE FROM SCHOOLS WHERE ID={$item['id']}"));
			$ch = !empty($item['checked']) ? '[X]' : '[ ]';
			$html .= str_repeat(' ', $item['__indent'] * 2) . "{$ch} {$ret[1]['TITLE']} - {$item['id']}\n";
		}
		$html .= "\n</pre>";
		return $html;
	}
}

if(!function_exists('iprint')) {
	//Immediate print
	function iprint() {
		$args = func_get_args();
		echo join(' ', $args) . "\n";
		flush();
	}
}

if(!function_exists('siprint')) {
	//Sprintf immediate print
	function siprint() {
		$args = func_get_args();
		echo call_user_func_array('sprintf', $args) . "\n";
		flush();
	}
}

if(!function_exists('SetupMemoryLimits')) {
	function SetupMemoryLimits() {
		if (!empty($GLOBALS['RECALC_DT_GRADES_MEMORY_LIMIT'])) {
			$limit = $GLOBALS['RECALC_DT_GRADES_MEMORY_LIMIT'];
			siprint("Applying memory limit from cron settings (%.2f MB)\n", $limit);

			if ($limit >= 1) {
				ini_set('memory_limit', "{$limit}M");
			}
			else {
				iprint("Invalid designated memory limit: $limit");
			}
		} else {
			siprint("No memory limit set, current value: %s", ini_get('memory_limit'));
		}
	}
}

if(!function_exists('RestrictSetForDebug')) {
	function RestrictSetForDebug($maxSections, &$quarters, &$sections_total) {
		$sections_total = 0;

		iprint("WARNING, RestrictSetForDebug WAS CALLED AND LIMITED SET TO {$maxSections} SECTIONS!");

		foreach ($quarters as $mpid => &$sections) {
			if ($sections_total >= $maxSections) {
				unset($quarters[$mpid]);
			}
			else {
				$section_count = count($sections['sections']);
				if ($sections_total + $section_count >= $maxSections) {
					$section_count = $maxSections - $sections_total;
					$sections['sections'] = array_slice($sections['sections'], 0, $section_count);
				}
				$sections_total += $section_count;
			}
		}
	}
}

if(!function_exists('DeleteDTGrades')) {
	function DeleteDTGrades($quarters) {
		global $students;
		iprint("WARNING, DeleteDTGrades WAS CALLED AND WILL DELETE ALL DT GRADES FOR SELECTED QUARTERS.");
		if ($students)
			$students_in = "AND STUDENT_ID IN (" . implode(',', $students) . ")";
		else
			$students_in = "";
		foreach ($quarters as $qtr) {
			$mpid = $qtr['quarter']['ID'];
			siprint('Deleting grades of "%s" (affect %s sections)', $mpid, count($qtr['sections']));

			foreach ($qtr['sections'] as $section) {
				$cpid = $section['COURSE_PERIOD_ID'];
				$sql = "DELETE
							FROM
						STUDENT_REPORT_CARD_GRADES
						WHERE
							MARKING_PERIOD_ID='DT{$mpid}' AND
							COURSE_PERIOD_ID='{$cpid}'
							{$students_in}";
				//iprint(preg_replace("/\\s+/", ' ', $sql));
				DBQuery($sql);
			}
		}
	}
}

if(!function_exists('ClearCache')) {
	function ClearCache() {
		siprint('Clearing cache at memory usage: %.2f MB', memory_get_usage(true) / 1024.0 / 1024.0);

		//unset($GLOBALS['_FOCUS']['Preferences']);
		foreach ($GLOBALS['_FOCUS']['Preferences'] as $key => $val) {
			if (strpos($key, 'Gradebook') === 0) {
				unset($GLOBALS['_FOCUS']['Preferences'][$key]);
			}
		}

		$GLOBALS['_FOCUS']['Preferences']['Preferences']['CacheRefreshPreferences'] = true;
		$GLOBALS['_FOCUS']['Preferences']['Preferences']['CacheRefreshGradebook'] = true;
		$GLOBALS['_FOCUS']['Preferences']['Preferences']['CacheRefreshGradebook_Options'] = true;

		unset($GLOBALS['_SESSION']['sql_log']);

		unset($GLOBALS['_SESSION']['sql_times']);

		DatabaseCache('clear', null);
	}
}

if(!function_exists('StartSectionChunk')) {
	function StartSectionChunk($quarters, &$state) {
		$state = [
			'iterator'    => new ArrayIterator($quarters),
			'section_idx' => 0
		];
	}
}

if(!function_exists('GetNextSectionChunk')) {
	function GetNextSectionChunk(&$state, $chunkCount) {
		$it          = $state['iterator'];
		$section_idx = $state['section_idx'];

		$results     = [];

		if (!$it->valid()) {
			return null;
		}

		while ($it->valid() && $chunkCount > 0) {
			$quarter = $it->current();

			$sections   = $quarter['sections'];
			$count      = count($sections) - $section_idx;
			$is_partial = $chunkCount < $count;

			if ($is_partial) {
				$count = $chunkCount;
			}

			$quarter['sections'] = array_slice($sections, $section_idx, $count);

			$results[] = $quarter;

			if ($is_partial) {
				$state['section_idx'] += $count;
				break;
			} else {
				$section_idx = $state['section_idx'] = 0;
			}

			$chunkCount -= $count;

			$it->next();
		}

		return $results;
	}
}

if(!function_exists('runCommand')) {
	function runCommand($command, &$stderr=null) {
		static $pipe_descriptors = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
		];

		// stderr on windows will always block on Windows
		$windows_stderr_bug = SVN::isWindowsEnvironment();

		if ($windows_stderr_bug) {
			$stderr_filename     = tempnam(sys_get_temp_dir(), 'svn_updater_stderr_');
			$pipe_descriptors[2] = ["file", $stderr_filename, "w"];

			// I know, close/invalidate the descriptor or something
			if (!is_writable($stderr_filename)) {
				$stderr = '(Could not create temp file for stderr)';
			}
		} else {
			$pipe_descriptors[2] = ['pipe', 'w'];
		}

		$process = proc_open($command, $pipe_descriptors, $pipes);

		$stderr = '';

		if (is_resource($process)) {
			$status = null;

			$fp_stdin  = $pipes[0];
			$fp_stdout = $pipes[1];
			$fp_stderr = $pipes[2];

			stream_set_blocking($fp_stdout, 0);
			stream_set_blocking($fp_stderr, 0);

			do {
				$last_total_read = 0;

				$data = fread($fp_stdout, 8192);
				if ($data !== '') {
					$last_total_read += strlen($data);
					// $stdout          .= $data;
					echo $data;
				}

				if (!$windows_stderr_bug) {
					$data = fread($fp_stderr, 8192);
					if ($data !== '') {
						$last_total_read += strlen($data);
						$stderr          .= $data;
					}
				}

				$status = proc_get_status($process);
			}
			while ($status['running']);

			// Start blocking to make sure we get all the data
			stream_set_blocking($fp_stdout, 1);

			while ( '' !== ($data = fread($fp_stdout, 8192)) ) {
				// $stdout .= $data;
				echo $data;
			}

			if (!$windows_stderr_bug) {
				stream_set_blocking($fp_stderr, 1);

				while ( '' !== ($data = fread($fp_stderr, 8192)) ) {
					$stderr .= $data;
				}
			}

			fclose($fp_stdin);
			fclose($fp_stdout);
			fclose($fp_stderr);

			proc_close($process);

			if ($windows_stderr_bug && file_exists($stderr_filename)) {
				// @todo Re-use $fp_stderr?
				$stderr = file_get_contents($stderr_filename);
				unlink($stderr_filename);
			}

			return $status['exitcode'];
		}

		return null;
	}
}

if(!function_exists('RecalculateDTGrades')) {
	function RecalculateDTGrades($quarters) {
		global $cd, $md, $memory_threshold, $display_interval;

		$GLOBALS['_FOCUS']['dont_load_plugin_engine'] = true;

		$old_session    = $GLOBALS['_SESSION'];

		//When we switch schools, we need to clear various caches.
		//So use this to keep track.
		$last_school = null;

		$quarters_total = count($quarters);

		$sections_count = 0;
		$updated        = 0;
		$empty_grade    = 0;
		$inserted       = 0;
		$matched        = 0;
		$quarters_idx   = 0;

		$sections_total = 0;

		foreach ($quarters as $qtr_idx => $qtr) {
			$sections_total += count($qtr['sections']);
		}

		// Make sure we can status updates for a small amount of
		if ($display_interval > $sections_total) {
			$display_interval = 1;
		}

		try {
			$cd->start('quarter_loop');
			foreach ($quarters as $qtr_idx => $qtr) {
				$current_period = $qtr['quarter']['ID'];
				siprint('Processing quarter "%s"', $qtr['title']);

				if ($last_school !== null && $last_school != $qtr['school_id']) {
					if ($last_school != $qtr['school_id'])
						siprint("Clearing cache because transitioning from school {$last_school} to {$qtr['school_id']}");
					ClearCache();
				}
				$last_school = $qtr['school_id'];

				$GLOBALS['_SESSION'] = $old_session;

				$cd->start('section_loop');

				$students_arr = array('cpid' => null, 'mpid' => null, 'insert' => array(), 'update' => array(), 'nope' => array(), 'empty' => array());
				foreach ($qtr['sections'] as $section) {
					$students_arr['cpid'] = $section['COURSE_PERIOD_ID'];
					$students_arr['mpid'] = $current_period;

					//if ($current_period == '765719' && ($students_arr['cpid'] == '30754' || $students_arr['cpid'] == '30715')) {
					//	$a = 0;
					//}

					$cd->start('session_setup');
					$sections_count++;
					$GLOBALS['_SESSION']['UserSchool'] = $GLOBALS['_REQUEST']['school_id'] = $section['SCHOOL_ID'];
					$GLOBALS['_SESSION']['UserSyear'] = $section['SYEAR'];

					//Thien: Originally the code used GetTeacher, which had the param $all by default. $all by default, for the first time,
					//causes GetTeacher grabs ALL teachers and caches them. But it caches all teachers by school_id. Not sure what the problem is,
					//but I'd shoot-a-pickle and say that teachers sometimes get removed and the school_id association is broken. So we
					//tend to get null sometimes.

					$teacher_info = DBMemcached(
						"SELECT
									STAFF_ID,
									LAST_NAME"
						. CCAT .
						"', '"
						. CCAT .
						"FIRST_NAME AS FULL_NAME,
									USERNAME,
									PROFILE,
									FIRST_NAME,
									LAST_NAME,
									TITLE,
									SCHOOLS,
									USERS.*
								FROM
									USERS
								WHERE
								STAFF_ID='{$section['TEACHER_ID']}'");

					$teacher_info = $teacher_info[1];

					$new_user = array(
						'STAFF_ID' => $section['TEACHER_ID'],
						'NAME' => $teacher_info['FULL_NAME'],
						'USERNAME' => DBEscapeString($teacher_info['USERNAME']),
						'PROFILE' => 'teacher',
						'SCHOOL_ID' => UserSchool());
					$cd->end();

					if (!$new_user['STAFF_ID']) {
						iprint("Section {$section['COURSE_PERIOD_ID']} does not have a teacher!");
						continue;
					}

					//if (!$new_user['USERNAME']) {
					//	iprint("$sections_count) Teacher {$new_user['STAFF_ID']} has no username");
					//}
					$GLOBALS['_FOCUS']['User'][1]['STAFF_ID'] = $new_user['STAFF_ID'];
					$GLOBALS['_FOCUS']['User'][1]['NAME'] = $new_user['NAME'];
					$GLOBALS['_FOCUS']['User'][1]['USERNAME'] = $new_user['USERNAME'];
					$GLOBALS['_FOCUS']['User'][1]['PROFILE'] = $new_user['PROFILE'];
					$GLOBALS['_FOCUS']['User'][1]['SCHOOL_ID'] = $new_user['SCHOOL_ID'];

					$current_teacher_name = trim($new_user['NAME']);
					if (empty($current_teacher_name)) {
						siprint('Teacher %s for course %s has no name!', $section['TEACHER_ID'], $section['COURSE_PERIOD_ID']);
					}

					//unset($new_user);

					$GLOBALS['_SESSION']['UserPeriod'] = $section['PERIOD_ID'];
					$GLOBALS['_SESSION']['UserCoursePeriod'] = $section['COURSE_PERIOD_ID'];
					$GLOBALS['_SESSION']['USERNAME'] = $new_user['USERNAME'];

					$course_period_id = UserCoursePeriod();

					$cd->start('course_info');
					$course_info_RET = DBMemcached(
						"SELECT
							c.GRAD_SUBJECT_ID,
							cp.COURSE_ID,
							cp.TITLE,
							cp.COURSE_WEIGHT,
							c.TITLE AS COURSE_TITLE,
							c.SHORT_NAME AS COURSE_NUM,
							cw.CREDITS,
							cp.DOES_GRADES,
							cp.GRADE_SCALE_ID,
							cp.DOES_GPA as AFFECTS_GPA,
							cp.MARKING_PERIOD_ID
						FROM
							COURSE_PERIODS cp
							LEFT JOIN
								COURSES c ON (c.COURSE_ID=cp.COURSE_ID)
							LEFT JOIN
								COURSE_WEIGHTS cw ON (cw.COURSE_ID=cp.COURSE_ID AND cw.COURSE_WEIGHT=cp.COURSE_WEIGHT)
						WHERE
							cp.COURSE_PERIOD_ID='" . UserCoursePeriod() . "'");
					$cd->end();

					//if($course_info_RET[1]['DOES_GRADES']!='Y') {
					//	iprint("Course Period ", UserCoursePeriod(), " does not do grades.");
					//	continue;
					//}
					global $grade_scale_id;
					$grade_scale_id = $course_info_RET[1]['GRADE_SCALE_ID'];
					$course_title = DBEscapeString($course_info_RET[1]['COURSE_TITLE']);
					$course_num = $course_info_RET[1]['COURSE_NUM'];
					$credits = $course_info_RET[1]['CREDITS'];
					$affects_gpa = $course_info_RET[1]['AFFECTS_GPA'];
					$grad_subject = $course_info_RET[1]['GRAD_SUBJECT_ID'];
					$course_weight = $course_info_RET[1]['COURSE_WEIGHT'];
					$course_id = $course_info_RET[1]['COURSE_ID'];
					unset($course_info_RET);

					$GLOBALS['_SESSION']['UserMP'] = $current_period;

					$profile = User('PROFILE');
					if ($profile != 'teacher')
						iprint("Warning, user profile \"{$profile}\" is not a teacher ({$profile}).");

					$cd->start('preference_refresh');

					unset($GLOBALS['course_info_cache']);

					ClearUserPreferencesCache();
					LoadPreferences([ $section['TEACHER_ID'] ], [ 'Gradebook' ]);
					GUser::resetCurrent();
					$cd->end('preference_refresh');
					$cd->start('grade_scale');
					$grade_scale_RET = DBMemcached(
						"SELECT
							ID,
							GPA_VALUE,
							WEIGHTED_GPA_VALUE,
							DEFAULT_BREAKOFF,
							CREDITS
						FROM
							REPORT_CARD_GRADES
						WHERE
							SCALE_ID='" . $grade_scale_id . "'
						ORDER BY
							SORT_ORDER",
						MEMCACHED_TIME,
						array(), array('TITLE'));
					$cd->end();

					$letter_grades_only = Preferences('LETTER_GRADES_NOT_POINTS', 'Gradebook') == 'Y';

					$cd->start('current_grades');
					$current_grades_RET = DBGet(DBQuery(
						"SELECT
							g.STUDENT_ID,
							g.PERCENT_GRADE
						FROM
							STUDENT_REPORT_CARD_GRADES g
						WHERE
							g.COURSE_PERIOD_ID='{$course_period_id}' AND
							g.MARKING_PERIOD_ID='DT{$current_period}'"),
						array(), array('STUDENT_ID'));
					$cd->end();


					$points_extra = array('SELECT_ONLY' => 'ssm.STUDENT_ID');
					// list students active as of the last day of school
					if (GetMP($current_period, 'END_DATE'))
						$points_extra['DATE'] = GetMP($current_period, 'END_DATE');
					$points_extra['mp_list'] = $section['MARKING_PERIOD_ID'];

					$cd->start('GetStuList');
					$students_RET = GetStuList($points_extra);
					$cd->end('GetStuList');

					if (empty($students_RET)) {
						iprint("No students for section {$course_period_id}");
					} else {

					}

					foreach ($students_RET as $student) {
						$student_id = $student['STUDENT_ID'];
						$GLOBALS['_SESSION']['STUDENT_ID'] = $student_id;

						$cd->start('GetCurrentGrade_percent');
						$percent = GetCurrentGrade($course_period_id,
							$student_id,
							$current_period,
							false,
							'percent', true);
						$cd->end('GetCurrentGrade_percent');

						if (empty($percent) && $percent !== 0 && $percent !== '0') {
							++$empty_grade;
							$students_arr['empty'][] = $student_id;
							//$percent = '-1';
							continue;
						}

						$total = $percent / 100;

						if ($letter_grades_only) {
							$cd->start('GetcurrentGrade_letter');
							$lg = GetCurrentGrade($course_period_id,
								$student_id,
								$current_period,
								[],
								'letter',
								true);
							$cd->end('GetcurrentGrade_letter');
						} else {
							$cd->start('_makeLetterGrade');
							$lg = _makeLetterGrade($total, UserCoursePeriod());
							$cd->end();
						}

						if ($grade_scale_RET[$lg][1]['CREDITS'] != 'Y')
							$carries_credits = '';
						else
							$carries_credits = 'Y';

						$course_history = '';

						//Also clear $percent if we're only doing letter grades because
						//STUDENT_REPORT_CARD_GRADES.PERCENT_GRADE is numeric.
						if ($percent == 'I' || $letter_grades_only)
							$percent = '';

						//If they already have a DT grade and it is not the same
						if ($current_grades_RET[$student_id] &&
							(!isset($current_grades_RET[$student_id][1]['PERCENT_GRADE'])
								|| $current_grades_RET[$student_id][1]['PERCENT_GRADE'] != $percent
								|| $current_grades_RET[$student_id][1]['GRADE_TITLE'] != $lg)
						) {
							$sql = "UPDATE
										STUDENT_REPORT_CARD_GRADES
									SET
										GRADE_TITLE='" . $lg . "',
										REPORT_CARD_GRADE_ID='" . $grade_scale_RET[$lg][1]['ID'] . "',
										PERCENT_GRADE='" . $percent . "',
										GPA_POINTS='" . ($grade_scale_RET[$lg][1]['GPA_VALUE']) . "',
										WEIGHTED_GPA_POINTS='" . ($grade_scale_RET[$lg][1]['WEIGHTED_GPA_VALUE']) . "',
										CREDITS='" . $credits . "',
										CARRIES_CREDITS='" . $carries_credits . "'
									WHERE
										STUDENT_ID='" . $student_id . "' AND
										COURSE_PERIOD_ID='" . $course_period_id . "' AND
										MARKING_PERIOD_ID='DT" . $current_period . "'";
							++$updated;
							$students_arr['update'][] = $student_id;
							if ($GLOBALS['_REQUEST']['display_queries'])
								iprint("Update student {$student_id} with grade {$percent} {$lg} for section {$course_period_id}");

						} else if (!$current_grades_RET[$student_id]) //If they do not have any DT grades at all
						{
							$sql = "INSERT INTO
										STUDENT_REPORT_CARD_GRADES
									(
										ID,
										SYEAR,
										SCHOOL_ID,
										STUDENT_ID,
										COURSE_PERIOD_ID,
										MARKING_PERIOD_ID,
										REPORT_CARD_GRADE_ID,
										COURSE_TITLE,
										TEACHER,
										GRAD_SUBJECT_ID,
										LOCATION_TITLE,
										PERCENT_GRADE,
										CREDITS,
										CARRIES_CREDITS,
										COURSE_NUM,
										AFFECTS_GPA,
										GPA_POINTS,
										WEIGHTED_GPA_POINTS,
										GRADE_TITLE,
										GRADE_SCALE_ID,
										COURSE_ID,
										COURSE_WEIGHT,
										COURSE_HISTORY
									)
									VALUES(
										" . db_seq_nextval('STUDENT_REPORT_CARD_GRADES_SEQ') . ",
										'" . UserSyear() . "',
										'" . UserSchool() . "',
										'" . $student_id . "',
										'" . $course_period_id . "',
										'DT" . $current_period . "',
										'" . $grade_scale_RET[$lg][1]['ID'] . "',
										'" . $course_title . "',
										'" . DBEscapeString($current_teacher_name) . "',
										'" . $grad_subject . "',
										'" . DBEscapeString(str_replace("'", "''", GetSchool(UserSchool()))) . "',
										'" . $percent . "',
										'" . $credits . "',
										'" . $carries_credits . "',
										'" . $course_num . "',
										'" . $affects_gpa . "',
										'" . ($grade_scale_RET[$lg][1]['GPA_VALUE']) . "',
										'" . ($grade_scale_RET[$lg][1]['WEIGHTED_GPA_VALUE']) . "',
										'" . $lg . "',
										'" . $grade_scale_id . "',
										'" . $course_id . "',
										'" . $course_weight . "',
										'" . $course_history . "'
									)";
							++$inserted;
							$students_arr['insert'][] = $student_id;
							if ($GLOBALS['_REQUEST']['display_queries'])
								iprint($sql);
							//iprint("Insert new grade for student {$student_id} with grade {$percent} {$lg} for section {$course_period_id}");
						} else { //Already have DT grades and they do match, so do nothing
							$students_arr['nope'][] = $student_id;
							++$matched;
							continue;
						}

						$rsrc = NULL;

						$actual_exception = false;
						try {
							$cd->start('dt_update_insert');
							$rsrc = DBQuery($sql, true, false, true, true);
							unset($_SESSION['sql_log']);
							$cd->end();
						} catch (Exception $e) {
							$actual_exception = true;
							$rsrc = NULL;
						}
						if (!$rsrc) {
							$pg_last_error = pg_last_error($GLOBALS['connection']);
							iprint("Could not execute query: \n\n\nBEGIN ERROR:\n\n{$sql}\n\nEND ERROR\n\n\nPG_LAST_ERROR:\n\n"
								. "{$pg_last_error}\n\n");
						}
					}

					unset($grade_scale_RET);
					unset($current_grades_RET);

					$mem_usage = $md->peek('elapsed', 'quarter_loop');

					if ($mem_usage > $memory_threshold) {
						$cd->start('ClearCache');

						$cd->start('Clear sections');
						unset($qtr['sections']);
						$cd->end();

						ClearCache();
						$cd->end();
					}

					if ($sections_count % $display_interval === 0) {
						siprint("{$sections_count}/{$sections_total} processed (insert: %d, update: %d, matched: %s, empty: %s, memory: %.3f mb)",
							$inserted, $updated, $matched, $empty_grade, memory_get_usage(true) / 1024.0 / 1024.0);
					}
				}

				if (!empty($GLOBALS['CRON_ARGS']['recalc_dt_affect_students']))
					iprint(print_r($students_arr, true));

				$cd->end('section_loop');

				if ($sections_count % $display_interval || $quarters_idx === $quarters_total - 1) {
					siprint("{$sections_count}/{$sections_total} processed (insert: %d, update: %d, matched: %s, empty: %s, memory: %.3f mb)",
						$inserted, $updated, $matched, $empty_grade, memory_get_usage(true) / 1024.0 / 1024.0);
				}

				$quarters_idx++;
				iprint("Quarter groups completed {$quarters_idx}/{$quarters_total}");
			}
			$cd->end('quarter_loop');
		}
		catch (Exception $e) {
			iprint("Exception occurred: " . $e->__toString());
		}

		unset($old_session['sql_log']);
		$GLOBALS['_SESSION'] = $old_session;
	}
}

if ($is_job) {
	$data_file = $arg_options['recalc-dt-grades-job'];

	if (!file_exists($data_file)) {
		iprint("File {$data_file} does not exist.");
		exit(1);
	}

	if (!is_readable($data_file)) {
		iprint("File {$data_file} is not readable");
		exit(1);
	}
	$contents = file_get_contents($data_file);

	unlink($data_file);

	$data = unserialize($contents);

	$GLOBALS['CRON_ARGS']                     = $data['cron_args'];
	$GLOBALS['memory_threshold']              = $data['memory_threshold'];
	$GLOBALS['display_interval']              = $data['display_interval'];
	$GLOBALS['RECALC_DT_GRADES_MEMORY_LIMIT'] = $data['memory_limit'];
	$chunk                                    = $data['chunk'];
	$chunk_no                                 = $data['chunk_no'];

	iprint("Performing job at {$chunk_no} as {$data_file}");


	SetupMemoryLimits();

	DBTransaction('BEGIN');

	RecalculateDTGrades($chunk);

	if ($GLOBALS['CRON_ARGS']['recalc_dry_run']) {
		iprint('Rolling back due to dry run.');
		DBTransaction('ROLLBACK');
	}
	else {
		iprint('Comitting...');
		DBTransaction('COMMIT');
	}

	unset($contents);

	exit(0);

} elseif ($GLOBALS['_REQUEST']['modfunc']=='run') {
	iprint("<pre>");

	SetupMemoryLimits();

	if (!empty($GLOBALS['RECALC_DT_GRADES_CACHE_LIMIT'])) {
		$limit = $GLOBALS['RECALC_DT_GRADES_CACHE_LIMIT'];
		siprint("Applying cache limit from cron settings ({$limit} MB)\n");
		$memory_threshold = 1024 * 1024 * $limit;
	}
	else
		siprint("Applying default cache limit of %.2f MB", $memory_threshold / 1024.0 / 1024.0);

	echo "<!-- Begin Run block -->\n";


	$cd->start('Run Block');

	$mp_html = '';
	$mp_selection = NULL;
	$rsection = $GLOBALS['_REQUEST']['section'];
	$rsection_list = '';
	$section_RET = NULL;

	if (!empty($GLOBALS['_REQUEST']['mp_select'])) {
		$mp_selection = $GLOBALS['_REQUEST']['mp_select'];
		$mp_selection = json_decode($mp_selection, true);
	} else {
		BackPrompt("Need at least one period.");
		return;
	}

	if ($GLOBALS['_REQUEST']['current_quarter']) {

		echo "<!-- Begin Quarter Filter -->\n";

		$cd->start('quarter_filter');
		$dbdate = DBDate();
		$default_syear = SystemPreferences('DEFAULT_S_YEAR');

		$current_period_where = "START_DATE IS NOT NULL AND END_DATE IS NOT NULL AND '{$dbdate}' >= START_DATE AND '{$dbdate}' <= END_DATE AND SYEAR='{$default_syear}'";
		$school_ids = array();
		foreach (new MPIterator($mp_selection, MPIterator::MODE_CHILDREN_FIRST) as $parent_mp => $item) {
			if ($item['type'] === 'schools')
				$school_ids[] = $item['id'];
		}


		$conditions = array(
			'schools'	=> array (
				'where'		=> $school_ids
			),
			'FY'		=> array (
				'where'		=> $current_period_where //SCHOOL_YEARS do not use start and end dates
			),
			'SEM'		=> array (
				'where'		=> $current_period_where
			),
			'QTR'		=> array (
				'where'		=> $current_period_where,
				'checked'	=> true
			)
		);
		$mp_selection = MPExplorer::CreateMPCollection($conditions);
		$cd->end('quarter_filter');

		echo "<!-- Quarter filter complete -->\n";
	}

	if (empty($mp_selection)) {
		echo "<!-- Failed due to no quarters -->\n";
		BackPrompt("No quarters could be found with given selection.");
		return;
	}

	if (empty($rsection)) {
		$mp_html = GetMPSelectionHTML($mp_selection);
	}
	else {
		$section_parts = explode(',', $rsection);
		$section_split = array();
		foreach ($section_parts as $s) {
			if (!is_numeric($s)) {
				echo "<!-- Failed due to invalid section number -->\n";
				BackPrompt("{$s} is not a valid section number.");
				return;
			}
			$section_split[] = trim($s);
		}
		$rsection_list = join(',', $section_split);
		$mp_html = "<pre>Single sections: {$rsection_list}</pre>";
	}

	$cancel = FALSE;

	if ($GLOBALS['_REQUEST']['school'] != 'all_schools'
		&& !Prompt('Confirm','Calculate DT grades for sections in these periods?', $mp_html, false, 'OK', null, null, 'post'))
		$cancel = TRUE;

	if (!$cancel)
	{

		if ($GLOBALS['CRON_ARGS']['recalc_dry_run']) {
			iprint("Dry run specified, will rollback at the end of transaction.");
		}

		$idx = 0;

		DBTransaction('BEGIN');

		$GLOBALS['_REQUEST']['modfunc'] = 'gradebook';

		$sections_RET = array();
		$section_fields = array(
			'MARKING_PERIOD_ID',
			'SCHOOL_ID',
			'TEACHER_ID',
			'COURSE_PERIOD_ID',
			'SYEAR',
			'PERIOD_ID',
			'DOES_GRADES');
		$fields = MPExplorer::RenderCPSelect($section_fields);

		$quarters = array();

		/**
		 * Whatever the user selects, we ultimately need to collect all quarters. After which, for each quarter,
		 * we build a marking period ID list we'll use to grab all our sections, this section is built from
		 * the quarter's parents.
		 */

		iprint('Preparing sections...');

		siprint('Current memory limit: %.3f MB', ini_get('memory_limit'));

		$quarter_count = 0;

		$extraCriteria = null;

		/*
		if ($GLOBALS['_REQUEST']['current_quarter']) {
			$dbdate = DBDate();
			$default_syear = SystemPreferences('DEFAULT_S_YEAR');
			$extraCriteria = array("QTR" => "START_DATE IS NOT NULL AND "
				. "END_DATE IS NOT NULL AND '{$dbdate}' >= START_DATE "
				. "AND '{$dbdate}' <= END_DATE AND SYEAR='{$default_syear}'");
			iprint("Current quarters requested, will only match quarters based\n\t"
				."on SYEAR \"{$default_syear}\" and START/END date \"{$dbdate}\"");
		}*/

		foreach (new MPIterator($mp_selection, MPIterator::MODE_CHILDREN_FIRST) as $parent_mp => $item) {
			$child_quarters = array();

			if ($item['type'] === 'QTR')
				$child_quarters[] = array('TYPE' => $item['type'], 'ID' => $item['id'], 'SCHOOL_ID' => $item['school_id']);

			MPExplorer::RetrieveChildPeriods($item['id'], $item['type'], $item['school_id'], 'QTR', $child_quarters/*, $extraCriteria*/);
			foreach ($child_quarters as $qtr) {
				$parents = MPExplorer::RetrieveParentChain($qtr['ID'], $qtr['TYPE'], $qtr['SCHOOL_ID']);
				$mp_info = MPExplorer::GetMPInfo($qtr['ID']);
				$quarters[] = array(
					'sections'	=> array(),
					'quarter'	=> $qtr,
					'parents'	=> $parents,
					'year'		=> $mp_info['SYEAR'],
					'title'		=> "{$mp_info['TITLE']} ({$qtr['ID']}) - {$mp_info['SYEAR']} - {$mp_info['SCHOOL_TITLE']}",
					'school_id' => $item['school_id']);
			}
			$quarter_count += count($child_quarters);
			unset($child_quarters);
		}

		siprint('Collected %d quarters', $quarter_count);

		$rsection_in = '';
		if (!empty($rsection)) {
			$rsection_in = " AND cp.COURSE_PERIOD_ID IN ({$rsection_list})";
			iprint("Will only include these sections:", $rsection_list);
		}

		global $students;
		$students = null;

		if (!empty($GLOBALS['CRON_ARGS']['recalc_dt_students'])) {
			$parts = explode(',', $GLOBALS['CRON_ARGS']['recalc_dt_students']);
			$students = array();
			foreach ($parts as $part)
				$students[] = trim($part);
			iprint('Will only include for students:', implode(', ', $students));
		}

		//Find sections that belong to each section's parent chain. For example,
		//a section belongs to quarter which belongs to semester. There may
		//be semester based classes whose marking_period_id is that semester marking period
		//These semester based courses have grades that belong to quarters, so a simple
		//search for sections based on quarters will not pick these up.
		$cd->start('sibling_sections');
		foreach ($quarters as $i => &$qtr) {
			$local_count = 0;
			$parent_count = count($qtr['parents']);
			$cp_where = '';
			$type_map = array('0' => 'FY');
			$type_desc = array();

			if ($parent_count === 1) {
				$cp_where = "cp.MARKING_PERIOD_ID={$qtr['parents'][0]['ID']}";
				$type_map[$qtr['parents'][0]['ID']] = $qtr['parents'][0]['TYPE'];
			}
			else {
				$ids = array();
				foreach ($qtr['parents'] as $mp) {
					$ids[] = $mp['ID'];
					$type_map[$mp['ID']] = $mp['TYPE'];
				}
				$cp_list = join(',', $ids);
				$cp_where = "cp.MARKING_PERIOD_ID IN ({$cp_list})";
			}

			$cp_where = "cp.GRADE_SCALE_ID IS NOT NULL AND cp.DOES_GRADES='Y' AND ({$cp_where} OR (cp.MARKING_PERIOD_ID=0 AND cp.SYEAR={$qtr['year']} AND cp.school_id={$qtr['school_id']}))";
			//Match any sections by the parent chain or FY

			if (empty($students))
				$sql = "SELECT
							{$fields}
						FROM
							COURSE_PERIODS cp
						WHERE
							{$cp_where}
							{$rsection_in}";
			else {
				$students_in = implode(',', $students);
				$sql = "SELECT
							DISTINCT cp.COURSE_PERIOD_ID,
							{$fields}
						FROM
							COURSE_PERIODS cp
							INNER JOIN SCHEDULE sch
								ON (sch.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID)
						WHERE
							{$cp_where}
							{$rsection_in} AND
							sch.STUDENT_ID IN ({$students_in})";
				$display_interval = 1;
			}

			$sections_RET = DBGet(DBQuery($sql), array(), array('MARKING_PERIOD_ID'));

			$this_total = 0;
			foreach ($sections_RET as  &$sections) {
				$this_total += count($sections);
			}

			if ($this_total === 0) {
				siprint('No sections for "%s"', $qtr['title']);
				unset($quarters[$i]);
				continue;
			}

			foreach ($sections_RET as $mpid => &$sections) {
				$count = count($sections);
				$type_desc[] = "{$type_map[$mpid]}: " . $count;
				$sections_count += $count;
				$local_count += $count;
				foreach ($sections as &$section)
					$qtr['sections'][] = $section;
			}

			if (!$type_desc)
				$type_desc = '';
			else
				$type_desc = " (" . join(', ', $type_desc) . ")";

			siprint('Collected %s sections %s for "%s"', $local_count, $type_desc, $qtr['title']);

			unset($sections_RET);
		}

		$quarters = array_values($quarters);

		unset($qtr);
		$cd->end('sibling_sections');

		iprint('Collected total of ', $sections_count, " sections.");
		$sections_total = $sections_count;
		$sections_count = 0;

		iprint('MP Selection:');
		iprint($mp_html);

		if (!empty($GLOBALS['CRON_ARGS']) && isset($GLOBALS['CRON_ARGS']['recalc_dt_limit'])) {
			RestrictSetForDebug(
				intval($GLOBALS['CRON_ARGS']['recalc_dt_limit']),
				$quarters,
				$sections_total);
		}

		if ($GLOBALS['CRON_ARGS']['recalc_dt_delete']) {
			DeleteDTGrades($quarters);
		}

		if (!empty($GLOBALS['CRON_ARGS']) && isset($GLOBALS['CRON_ARGS']['recalc_dt_benchmark'])) {
			iprint('Not proceeding with recalc as --recalc_dt_benchmark was specified.');
			iprint("\n" . $cd->summary());
			DBTransaction('COMMIT');
			return;
		}

		$memory_capture = memory_get_usage(true);

		//TODO: wtf, for jackson the cpid's 52640,30715,30754 will work for the first but
		//not the rest!

		$php_cli     = null;
		$command_pre = null;

		// Deploy jobs if there are more than a job workload
		if ($sections_per_job < $sections_total) {
			try {
				// Use the SVN class to retrieve the proper PHP CLI executable
				// Override permissions since this will be run by cli
				$GLOBALS['_FOCUS']['allow_edit'] = true;
				$GLOBALS['_FOCUS']['allow_use']  = true;

				$svn = new SVN(__DIR__ . "/..");

				$php_cli = $svn->locatePHPCLIExecutable();

				unset($GLOBALS['_FOCUS']['allow_edit']);
				unset($GLOBALS['_FOCUS']['allow_use']);

				// Build a pre-formatted command string

				$file = __FILE__;

				$command_pre = "{$php_cli} {$file} --recalc-dt-grades-job=%s";

			} catch (Exception $e) {
				iprint("Exception while instantiating SVN object: " . $e->__toString());
			}
		}

		iprint("Doing {$sections_per_job} section(s) per chunk");

		$chunk_state = null;

		StartSectionChunk($quarters, $chunk_state);

		$total_chunks = ceil($sections_total / $sections_per_job);

		$commit = true;
		for ($i = 0; ($chunk = GetNextSectionChunk($chunk_state, $sections_per_job)) !== null; ++$i) {
			$chunk_no = $i + 1;
			iprint("Doing chunk {$chunk_no} / {$total_chunks}");
			// Don't deploy jobs if we couldn't find a php CLI
			if ($php_cli === null) {
				RecalculateDTGrades($chunk);
			} else {
				// Write out the next chunk
				$data = [
					'chunk'            => $chunk,
					'cron_args'        => $GLOBALS['CRON_ARGS'],
					'memory_threshold' => $GLOBALS['memory_threshold'],
					'display_interval' => $GLOBALS['display_interval'],
					'memory_limit'     => $GLOBALS['RECALC_DT_GRADES_MEMORY_LIMIT'],
					'chunk_no'         => $chunk_no
				];

				$temp_file = tempnam(sys_get_temp_dir(), 'recalc_dt_grades_job_');

				if (file_put_contents($temp_file, serialize($data)) === false) {
					iprint('Could not write temp file for job!');
				} else {
					$command = sprintf($command_pre, $temp_file);
					$stderr  = '';

					$return = runCommand($command, $stderr);

					if (file_exists($temp_file)) {
						unlink($temp_file);
					}

					iprint("Job completed with return code {$return}");

					if (!empty($stderr)) {
						iprint("Standard Error from Job: \n\n{$stderr}\n");
					}
					$commit = false;
				}
			}
		}

		$cd->start('commit/rollback');
		if ($GLOBALS['CRON_ARGS']['recalc_dry_run']) {
			iprint('Rolling back due to dry run.');
			DBTransaction('ROLLBACK');
		} elseif ($commit) {
			iprint('Comitting...');
			DBTransaction('COMMIT');
		}
		$cd->end();

		siprint("Completed. (%.3f mb)", memory_get_usage(true) / 1024.0 / 1024.0);

		$cd->start('ClearCache');
		$cd->start('Clear quarters');
		unset($quarters);
		$cd->end();
		ClearCache();
		$cd->end();

		$cd->end('Run Block');
		iprint("\n" . $cd->summary());
	}

	iprint("</pre>");
}
else
{
	echo "<BODY><CENTER><TABLE width=100% cellpadding=0 cellspacing=0><TR><TD valign=top>";
	echo '<BR>';
	$message = 'Choose a progress period to post grades for all teachers.<BR><BR>
	<FORM action='.$GLOBALS['_SERVER']['PHP_SELF'].'?modfunc=run method="post"><TABLE><TR><TD align=right>School / Progress Period</TD><TD>
	<div>
	<div id="filter_box_heading"></div>';

	$mpe = new MPExplorer('schools', null, 'mpe_control', 'Choose a semester (quarter)', 'QTR');

	ob_start();
	?>
	<script type="text/javascript" src="<?php echo dirname(dirname($GLOBALS['_SERVER']['SCRIPT_NAME'])) . "/assets/jquery/jquery.js"; ?>"></script>
	<?php
	$message .= ob_get_clean();
	$message .= $mpe->GenerateControl('mp_select');
	$message .= $mpe->GenerateJavascript();
	$message .= $mpe->GenerateDefaultCSS();
	$message .= '</div>';

	$message .= '
	</TD><TD><div id=pool_view_div></div></TD></TR>
	<TR><TD><label><input type=checkbox name=display_queries value=Y />Display update queries</label></TD></TR>
	<TR><TD><label><input type=checkbox name=current_quarter value=Y />Select current quarters selected schools</input></label>';

	ob_start();
	?>
	<script>
		$('input[name=current_quarter]').click(function () {
			//mpe_Instances.mp_select.Disable($(this).prop('checked') ? '');
			mpe_Instances.mp_select.LimitMP($(this).prop('checked') ? 'schools' : 'QTR');
		});
	</script>
	<?php

	$message .= ob_get_clean() . '</TD</TR>
	</TABLE>

	Include only specific sections: <input type="edit" name="section"></input>
	<BR/><BR/>

	<INPUT type=submit value="Run"></FORM> &nbsp; &nbsp; &nbsp; ';

	DrawBlock('',$message);
	unset($GLOBALS['_SESSION']['sql_log']);
	echo '</BODY>';
	echo '</HTML>';
}


?>
