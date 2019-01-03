<?php

class MigrationsController {
	private static $cancelled = "\n\n-- Migrations Cancelled --\n\n";

	/**
	 * Get all the migrations that have not already been run
	 * @return Array An array of migration information
	 */
	public function getMigrations() {
		Migrations::checkUsername();

		$paths      = \Admin\Util::getMigrationPaths();
		$files      = Migrations::getMigrations($paths);
		$migrations = [];

		foreach($files as $migration_id => $migration) {
			$migrations[] = [
				'date'         => null,
				'migration_id' => $migration_id,
				'output'       => null,
				'svn_date'     => null,
				'svn_revision' => null,
				'svn_url'      => null,
				'time'         => null,
				'deleted'      => null
			];
		}

		return $migrations;
	}

	/**
	 * Get all the migrations that have already been run
	 * @return Array An array of completed migration information
	 */
	public function getCompleted() {
		Migrations::checkUsername();

		// Truncate output longer than this threshold
		$threshold  = floor(0.25 * 1024 * 1024);

		$output_str = <<<TEXT
The output was truncated. Please run the following query to view the output for this migration:

SELECT
	migration_id,
	output
FROM
	database_migrations
WHERE
	id = ~~ID~~
TEXT;

		$template = json_encode([
			'sql' => [
				'sql_log'         => [],
				'sql_times'       => [],
				'sql_fetch_times' => [],
				'sql_cache_times' => []
			],

			'output' => $output_str
		]);

		$text_type = Database::$type === 'mssql' ? 'VARCHAR(MAX)' : 'TEXT';

		$output_sql = Database::preprocess("
			CASE WHEN {{length:output}} > :threshold THEN
				REPLACE(:template, '~~ID~~', CAST(id AS {$text_type}))
			ELSE
				output
			END
		");

		$sql = "
			SELECT
				id,
				migration_id,
				date,
				time,
				({$output_sql}) AS output,
				svn_url,
				svn_revision,
				svn_date,
				deleted
			FROM
				database_migrations
		";

		$params = [
			'threshold' => $threshold,
			'template'  => $template,
		];

		return array_values(array_map(function($row) {
			$row             = array_change_key_case($row, CASE_LOWER);
			$row['time']     = round($row['time'], 2);
			$row['_time']    = number_format($row['time'], 2);
			$row['svn_date'] = date('Y-m-d H:i:s', strtotime($row['svn_date']));
			$row['date']     = date('Y-m-d H:i:s', strtotime($row['date']));

			return $row;
		}, Database::get($sql, $params)));
	}

	/**
	 * Soft-delete a migration record
	 * @param String $id The ID of the migration record
	 */
	public function deleteMigration($id) {
		Migrations::checkUsername();

		$sql = "
			UPDATE
				database_migrations
			SET
				deleted = 1
			WHERE
				id = :id
		";

		$params = [
			'id' => $id
		];

		Database::query($sql, $params);
	}

	/**
	 * Start running migrations
	 */
	public function startMigrations() {
		Migrations::checkUsername();

		$manage_dir = realpath(__DIR__ . '/..');
		$root_dir   = realpath("{$manage_dir}/../../");
		$log_file   = "{$manage_dir}/.migration.log";
		$php_file   = "{$manage_dir}/.migration.php";
		$table      = '__migrations__';

		Database::begin();

		// Check for the migrations table
		if(!Database::tableExists($table)) {
			// Create the migrations table
			Database::query("
				CREATE TABLE {$table} (
					pid BIGINT NOT NULL,
					total BIGINT NOT NULL,
					current_migration VARCHAR(255) NULL
				)
			");

			// Write and execute the PHP script
			$cancelled = self::$cancelled;

			$php = <<<PHP

			<?php

			\$runningCron = true;

			require_once("{$root_dir}/Warehouse.php");
			Menu::loadMenuIncludes();

			ini_set('display_errors', 1);
			error_reporting(E_ALL);

			\$paths = \Admin\Util::getMigrationPaths();

			try {
				Migrations::migrate(\$paths);
			}
			catch(Exception \$e) {
				\$message = [
					"{$cancelled}",
					\$e->getMessage()
				];

				throw new Exception(join("\n", \$message));
			}

			// Drop the table
			Database::begin();

			if(Database::tableExists('{$table}')) {
				Database::query("DROP TABLE {$table}");
			}

			Database::commit();

			// Delete the log file and this script
			@unlink("{$log_file}");
			@unlink("{$php_file}");
PHP;

			// Write the migrations script
			file_put_contents($php_file, $php);

			// Get the path to the PHP binary
			$bin = defined('UPDATER_PHP_CLI_PATH') ? UPDATER_PHP_CLI_PATH : 'php';

			// Run the script and return the PID
			exec("{$bin} {$php_file} > {$log_file} 2>&1 & echo $!", $output);

			// Get the process ID
			$pid = intval($output[0]);

			// Get the number of pending migrations
			$paths = \Admin\Util::getMigrationPaths();
			$total = count(Migrations::getMigrations($paths));

			// Write the current status to the database
			$params = [
				'pid'   => $pid,
				'total' => $total
			];

			Database::query("
				INSERT INTO {$table} (
					pid,
					total
				)
				VALUES (
					:pid,
					:total
				)
			", $params);
		}

		Database::commit();
	}

	/**
	 * Stop any currently running migrations
	 */
	public function stopMigrations() {
		Migrations::checkUsername();

		$manage_dir = realpath(__DIR__ . '/..');
		$log_file   = "{$manage_dir}/.migration.log";
		$php_file   = "{$manage_dir}/.migration.php";
		$table      = '__migrations__';

		Database::begin();

		if(Database::tableExists($table)) {
			$rows = Database::get("SELECT pid FROM {$table}");
			$row  = reset($rows);

			if(!empty($row)) {
				$pid = intval($row['PID']);
				@exec("kill -9 {$pid}");
			}

			Database::query("DROP TABLE {$table}");
		}

		Database::commit();

		@unlink($log_file);
		@unlink($php_file);
	}

	/**
	 * Get the status of the currently running migration (if any)
	 * @return Array An array containing the process ID, total number of migrations, and current migration number
	 */
	public function getStatus() {
		Migrations::checkUsername();

		$manage_dir = realpath(__DIR__ . '/..');
		$log_file   = "{$manage_dir}/.migration.log";
		$php_file   = "{$manage_dir}/.migration.php";
		$table      = '__migrations__';

		if(file_exists($log_file)) {
			$error     = trim(file_get_contents($log_file));
			$cancelled = trim(self::$cancelled);

			if(!empty($error)) {
				if(strpos($error, $cancelled) === false) {
					// If a fatal error was not thrown, just print the warning
					echo $error;

					// And clear the log file
					file_put_contents($log_file, '');
				}
				else {
					// Otherwise, stop the process
					$this->stopMigrations();

					return [
						'total'   => null,
						'pending' => null,
						'error'   => $error
					];
				}
			}
		}

		if(Database::tableExists($table)) {
			$rows = Database::get("SELECT total, current_migration FROM {$table}");
			$row  = reset($rows);

			if(!empty($row)) {
				$total   = intval($row['TOTAL']);
				$paths   = \Admin\Util::getMigrationPaths();
				$pending = Migrations::getMigrations($paths);

				return [
					'total'   => $total,
					'pending' => count($pending),
					'current' => $row['CURRENT_MIGRATION'],
					'error'   => null
				];
			}
		}

		return null;
	}
}
