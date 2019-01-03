<?php
/**
 * @author Tom Wilson <tomw@focusschoolsoftware.com>
 */

$debug = $_GET['debug'] === 'true';
$force = $_GET['force'] === 'true';

if($debug || $force) {
	require_once('../Warehouse.php');
}

MigrationFOCUS16628::run($debug);

class MigrationFOCUS16628 {
	private static
		$debug = false,

		$columns = [],

		$indexes = [],

		$indexes_to_rebuild = [],

		$table = 'student_gpa_calculated',

		$mssql_float_cols = [
			'cumulative_gpa',
			'cumulative_weighted_gpa',
			'gpa',
			'weighted_gpa',
			'custom_1_gpa',
			'custom_2_gpa',
			'custom_3_gpa',
			'custom_4_gpa',
			'custom_5_gpa',
		],

		$smallint_cols = [
			'class_rank',
			'weighted_class_rank',
			'unweighted_class_rank',
			'custom_1_rank',
			'custom_2_rank',
			'custom_3_rank',
			'custom_4_rank',
			'custom_5_rank',
			'grade_total',
		];

	public static function run($enable_debugging = false) {
		self::$debug = $enable_debugging;

		if(self::$debug) {
			echo "<pre>";
		}

		if(Database::$type === 'mssql') {
			$data_type = 'FLOAT';
			$precision = 53;

			foreach(self::$mssql_float_cols as $float_col) {
				if(self::needsTypeChange($float_col, $data_type, $precision)) {
					self::dropIndexesContaining($float_col);
					self::changeColumnType(self::$table, $float_col, $data_type, $precision);
				}
			}
		}

		$data_type = 'SMALLINT';

		foreach(self::$smallint_cols as $smallint_col) {
			if(self::needsTypeChange($smallint_col, $data_type)) {
				self::dropIndexesContaining($smallint_col);
				self::changeColumnType(self::$table, $smallint_col, $data_type);
			}
		}

		self::rebuildDroppedIndexes();

		if(self::$debug) {
			var_dump(Database::getColumns('student_gpa_calculated'));
		}

		self::populateBaseClassRankOnSysPref();
	}

	private static function needsTypeChange($col_name, $data_type, $precision = null) {
		$columns = self::getColumns();

		$col_name = strtolower($col_name);

		// If the column doesn't exist, it cannot have a type change. It needs to be created altogether.
		if(!isset($columns[$col_name])) {
			$length = isset($precision) ? $precision : '';

			Database::createColumn(self::$table, $col_name, $data_type, $length);

			return false;
		}

		$col_info = $columns[$col_name];

		if(strtolower($data_type) !== strtolower($col_info['DATA_TYPE'])) {
			return true;
		}

		if(isset($precision) && intval($precision) !== intval($col_info['NUMERIC_PRECISION'])) {
			return true;
		}

		return false;
	}

	private static function dropIndexesContaining($col_name) {
		$table = self::$table;

		foreach(self::getIndexes() as $index_name => $index_cols) {
			if(array_search($col_name, $index_cols) !== false && array_search($index_name, self::$indexes_to_rebuild) === false) {
				self::$indexes_to_rebuild[] = $index_name;

				self::query("DROP INDEX {$index_name} ON {$table}");
			}
		}
	}

	private static function rebuildDroppedIndexes() {
		$table = self::$table;

		foreach(self::$indexes_to_rebuild as $index_name) {
			$index_cols_str = join(', ', self::getIndexes()[$index_name]);

			self::query("CREATE INDEX {$index_name} ON {$table} ({$index_cols_str})");
		}
	}

	private static function getColumns() {
		if(!empty(self::$columns)) {
			return self::$columns;
		}

		return self::$columns = Database::getColumns(self::$table);
	}

	private static function getIndexes() {
		if(!empty(self::$indexes)) {
			return self::$indexes;
		}

		return self::$indexes = Database::getIndexes(self::$table);
	}

	private static function query($query) {
		if(self::$debug) {
			echo "{$query}<br>";
		}
		else {
			Database::query($query);
		}
	}

	private static function changeColumnType() {
		$args = func_get_args();

		if(self::$debug) {
			print_r($args);
		}
		else {
			call_user_func_array('Database::changeColumnType', $args);
		}
	}

	/**
	 * If a custom GPA exists, set the district to use the custom GPA by default and
	 * do not override the schools. Otherwise, if there is no custom GPA, set this according
	 * to the "Use Weighted GPA" setting.
	 */
	private static function populateBaseClassRankOnSysPref() {
		$class_rank_pref_title = 'BASE_CLASS_RANK_ON';

		$has_pref = Database::get("SELECT 1 FROM program_config WHERE title = '{$class_rank_pref_title}'");

		// If the preference has already been set, bail out.
		if(!empty($has_pref)) {
			return;
		}

		$custom_config = isset($GLOBALS['_FOCUS']['config']['STUDENT_GPA_CALCULATED']['CUSTOM_1_GPA']) ? $GLOBALS['_FOCUS']['config']['STUDENT_GPA_CALCULATED']['CUSTOM_1_GPA'] : [];

		$syear = getDefaultSyear();

		// If there is a custom config that is used for class rank, use that as the district default.
		if(!empty($custom_config) && isset($custom_config['class_rank']) && $custom_config['class_rank']) {
			self::query("INSERT INTO program_config (syear, program, title, value) VALUES ({$syear}, 'system', '{$class_rank_pref_title}', 'CUSTOM_1_GPA')");

			return;
		}

		$district_uses_weighted_gpa = SystemPreferences('WEIGHTED_GPA', 'system', true, false, $syear) === 'Y';

		$column = $district_uses_weighted_gpa ? 'WEIGHTED_GPA' : 'GPA';

		self::query("INSERT INTO program_config (syear, program, title, value) VALUES ({$syear}, 'system', '{$class_rank_pref_title}', '{$column}')");

		foreach(SISSchool::getAll() as $school) {
			$school_id = $school->getId();

			$school_uses_weighted_gpa = SystemPreferences('WEIGHTED_GPA', 'school_prefs', false, $school_id, $syear) === 'Y';

			// If the district pref and the school's pref do not match, then the school
			// has overridden the default pref. Insert the school's pref to match the override.
			if($district_uses_weighted_gpa !== $school_uses_weighted_gpa) {
				$column = $school_uses_weighted_gpa ? 'WEIGHTED_GPA' : 'GPA';

				self::query("INSERT INTO program_config (syear, school_id, program, title, value) VALUES ({$syear}, {$school_id}, 'school_prefs', '{$class_rank_pref_title}', '{$column}')");
			}
		}
	}
}

