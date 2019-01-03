<?php
// Tags: SSS, Formbuilder
// return false;
set_time_limit(0);
ini_set('memory_limit', '8G');

// error_reporting(1);
// ini_set('display_errors', 1);

use Focus\Formbuilder\Form;
use Focus\Formbuilder\FormComponent;
use Focus\Formbuilder\FormField;
use Focus\Formbuilder\FormRevision;
use Focus\FormBuilder\FormService;

// if (!class_exists('Database')) {
// 	$runningCron = true;
// 	require_once(__DIR__ . "/../Warehouse.php");
// }

if (!class_exists('\Focus\FormBuilder\Migrations\Version3')) {
	throw new Exception("Please run composer install.");
}

// Way too many queries to insert into DB, we'll put it back when migration finishes
$do_sql_log = $_SESSION['do_sql_log'];
$_SESSION['do_sql_log'] = false;

Migrations::depend('FOCUS-8436');
Migrations::depend('FOCUS-9271');
Migrations::depend('FOCUS-9271b');
Migrations::depend('FOCUS-10055');
Migrations::depend('FOCUS-10647');
Migrations::depend('FOCUS-12796');
Migrations::depend('FOCUS-10794');
Migrations::depend('FOCUS-16553');

Database::commit();

if ($runningCron) {
	echo "Loading forms into memory..." . PHP_EOL;
}

// Prior to this branch we could not track who made updates to the form, so
// will just fill in the blank with the focus user
$focus_user = Database::get("SELECT staff_id FROM users WHERE username='focus'");
$focus_user = $focus_user[0]['STAFF_ID'];

if (!Database::columnExists('sss_form_instances', 'raw_data')) {
	$form_records = [];
} else {
	$form_records = Database::get("
		SELECT f.id AS form_id, f.language_id, f.name, f.deleted_at, f.folder, f.updated_at, f.layout, COUNT(*) AS instance_count
		FROM sss_forms f
		LEFT JOIN sss_form_instances fi ON (
			f.id = fi.form_id
			AND fi.deleted_at IS NULL
			AND fi.raw_data IS NOT NULL
			AND fi.raw_data != '{\"firstLoad\":false}'
			AND COALESCE(fi.saved, fi.drafted) IS NOT NULL
		)
		WHERE f.new_form_id IS NULL AND ((fi.id IS NOT NULL AND fi.deleted_at IS NULL) OR f.deleted_at IS NULL)
		GROUP BY f.id, f.language_id, f.name, f.deleted_at, f.folder, f.updated_at, f.layout
		ORDER BY instance_count ASC
	");
}

$total_instances          = array_sum(array_column($form_records, 'INSTANCE_COUNT'));
$total_instances_complete = 0;

if ($runningCron) {
	echo "Migrating {$total_instances} instances" . PHP_EOL;
}

if (!Database::columnExists('sss_form_instances', 'new_form_id')) {
	Database::createColumn('sss_form_instances', 'new_form_id', 'bigint');
	Database::createColumn('sss_form_instances', 'new_instance_id', 'bigint');
}

// In accordance with JIRA FOCUS-16599.
$cascade = "";

// We'll be hitting this table hard with update queries, properly index if not already

if (Database::$type === "postgres") {
	Database::query("REINDEX TABLE sss_form_instances");
	$cascade = ' CASCADE';
}

foreach ($form_records as $form_index => $form_record) {
	Database::begin();

	$old_form_id     = $form_record['FORM_ID'];
	$instance_count  = $form_record['INSTANCE_COUNT'];
	$select_raw_data = $instance_count < 8000 ? 'raw_data,' : '';

	// We order by created_at to generate revisions in a sequence
	// as close to accurate as possible to speed up consolidation of duplicate
	// revisions (queries are cached)
	$instance_records = Database::get("
		SELECT id, {$select_raw_data} created_at, staff_id, saved, drafted FROM sss_form_instances
		WHERE deleted_at IS NULL
		  AND form_id = {$old_form_id}
		  AND raw_data IS NOT NULL
		  AND raw_data != '{\"firstLoad\":false}'
		  AND COALESCE(saved, drafted) IS NOT NULL
		ORDER BY created_at ASC
	");

	$form = (new Form())
		->setName($form_record['NAME'])
		->setLanguageId($form_record['LANGUAGE_ID'])
		->setFolder($form_record['FOLDER'])
		->setDeletedAt($form_record['DELETED_AT'])
		->persist(false);

	$new_form_id = $form->getId();

	// These don't have any instances that need new ids, so just set the form id here
	Database::query("UPDATE sss_form_instances SET new_form_id = {$new_form_id} WHERE form_id = {$old_form_id} AND (saved IS NULL OR raw_data IS NULL)");

	// I'm writing this under the assumption that more recent forms with the same
	// negative number component id will be the exact same component. I don't
	// think this should cause issue. It is verified with the hash to be sure.
	$old_new = [];

	foreach ($instance_records as $instance_index => $instance_record) {
		$has_draft = !empty($instance_record['DRAFTED']);

		if ($has_draft) {
			if (is_null($instance_record['SAVED'])) {
				$first_save = $instance_record['DRAFTED'];
			} else {
				$first_save = strtotime($instance_record['SAVED']) < strtotime($instance_record['DRAFTED']) ? $instance_record['SAVED'] : $instance_record['DRAFTED'];
			}
		} else {
			$first_save = $instance_record['SAVED'];
		}

		if ($runningCron) {
			if (function_exists('posix_isatty') && posix_isatty(STDOUT)) {
				// Clear screen and move cursor to 0, 0
				echo "\033[2J";
				echo "\033[0;0H";
			} else {
				echo PHP_EOL . PHP_EOL;
			}
		}

		$status = [
			'Current Form:'      => $old_form_id,
			'Current Instance:'  => $instance_record['ID'],
			'Total Forms:'       => ($form_index + 1) . " / " . count($form_records) . " (" . number_format($total_instances) . " instances)",
			'Form Instances:'    => ($instance_index + 1) . " / {$instance_count}",
			'Instance Progress:' => floor(($instance_index + 1) / $instance_count * 100) . '%',
			'Total Progress:'    => floor($total_instances_complete++ * 100 / $total_instances) . '%'
		];

		if ($runningCron) {
			foreach ($status as $title => $value) {
				echo str_pad($title, 19, ' ', STR_PAD_RIGHT);
				echo $value . PHP_EOL;
			}
		}

		if (empty($select_raw_data)) {
			$raw_data = Database::get("SELECT raw_data FROM sss_form_instances WHERE id = " . $instance_record['ID']);
			$raw_data = $raw_data[0]['RAW_DATA'];
		} else {
			$raw_data = $instance_record['RAW_DATA'];
		}

		$layout = json_decode($raw_data);
		$layout = FormService::migrate($layout);
		$models = array_values((array)$layout->components);

		// Normalize values that are changed programatically anyways for compression
		foreach ($models as &$model) {
			if (strpos($model->generalCode, 'this.setEnabled') !== false) {
				$model->enabled = true;
			}

			if (strpos($model->generalCode, 'this.setRequired') !== false) {
				$model->required = false;
			}
		}
		unset($model);

		$collections = Database::get("
			SELECT name, sql AS query
			FROM sss_form_collections
			WHERE form_id = {$old_form_id} AND (
				deleted_at IS NULL OR deleted_at > '{$first_save}'
			)
		");

		foreach ($collections as &$collection) {
			$collection = array_change_key_case($collection);
			$collection['form_id'] = $new_form_id;
		}
		unset($collection);

		$head = $form->getHeadRevision();
		if (!empty($collections) && $head !== null) {
			$existing_collections = Database::get("
				SELECT id, name, query
				FROM formbuilder_collections
				WHERE form_id = {$new_form_id}
				  AND created_revision <= {$head}
				  AND (removed_revision IS NULL OR removed_revision > {$head})
			");

			// Determines if the collection exists in the new table, if so an id is added
			// so the save function can add a removed revision
			foreach ($existing_collections as &$c) {
				foreach ($collections as &$collection) {
					if ($collection['name'] === $c['NAME']) {
						if ($collection['query'] === $c['QUERY']) {
							$collection['id'] = $c['ID'];
						} else {
							continue 2;
						}
					}
				}
			}
			unset($c, $collection);
		}

		// If there are any differences in structure, we'll create a new revision
		$actions = [];

		$revision_id = $form->save($models, $collections, $actions, $focus_user, $first_save, $old_new, true);
		$form->setHeadRevision($revision_id);

		// Organize data into new format
		$form_data = [];
		foreach ($models as $model) {
			if (!isset($model->value)) {
				continue;
			}

			if (!array_key_exists($model->id, $old_new)) {
				print_r($models);
				print_r($old_new);
				throw new Exception("Something wrong with old_new, model id: " . $model->id);
			}

			$form_data[$old_new[$model->id]] = $model->value;
		}

		// Create the instance based on the HEAD revision now that the form has been saved
		$form_instance = $form->createInstance($form_data, $instance_record['STAFF_ID'], $revision_id);
		$instance_id   = $form_instance->getId();

		// If draft saved before final saved, then raw_data is not the draft
		$no_draft     = empty($instance_record['DRAFTED']);
		$not_complete = empty($instance_record['SAVED']);

		if ($no_draft || (!$not_complete && $first_save === $instance_record['DRAFTED'])) {
			$draft_instance_id = "null";
		} else if ($not_complete) {
			$draft_instance_id = $instance_id;
			$instance_id       = "null";
		} else {
			$saved_values = Database::get("SELECT field_id, value FROM sss_form_field_instances WHERE form_id = {$old_form_id} AND form_instance_id = {$instance_record['ID']}");
			$saved_values = array_column($saved_values, 'VALUE', 'FIELD_ID');
			foreach ($models as $model) {
				if (!property_exists($model, 'value')) {
					continue;
				}

				$field_id = $old_new[$model->id];

				if (!key_exists($model->id, $saved_values)) {
					unset($form_data[$field_id]);
				} else {
					$form_data[$field_id] = $saved_values[$model->id];
				}
			}

			$form_instance     = $form->createInstance($form_data, $instance_record['STAFF_ID'], $revision_id);
			$draft_instance_id = $instance_id;
			$instance_id       = $form_instance->getId();
		}

		Database::query("
			UPDATE sss_form_instances SET
				new_form_id       = {$new_form_id},
				new_instance_id   = {$instance_id},
				draft_instance_id = {$draft_instance_id}
			WHERE id = " . $instance_record['ID']
		);

		// Fast if no new revisions, but RAM's gonna have a bad day if a lot of instances...
		if ($total_instances_complete % 500 === 0) {
			FormComponent::clearDataCache();
			FormField::clearDataCache();
			Form::clearDataCache();
			FormRevision::clearDataCache();
		}

		// Balance btrees
		if (Database::$type === "postgres" && $total_instances_complete % 1000 === 0) {
			Database::query("REINDEX TABLE formbuilder_components");
			Database::query("REINDEX TABLE formbuilder_objects");
		}

	}

	// All revisions have been generated, but the live form may have a revision
	// that has not yet been an instance yet, so we'll check that now.
	$layout = FormService::migrate(json_decode($form_record['LAYOUT']));
	$models = array_values((array)$layout->components);

	$collections = Database::get("SELECT name, sql AS query FROM sss_form_collections WHERE form_id = {$old_form_id} AND deleted_at IS NULL");
	foreach ($collections as &$collection) {
		$collection = array_change_key_case($collection);
		$collection['form_id'] = $new_form_id;
	}
	unset($collection);

	if (!empty($collections)) {
		$head   = $form->getHeadRevision();
		if (is_null($head)) {
			$existing_collections = [];
		} else {
			$existing_collections = Database::get("
				SELECT id, name, query
				FROM formbuilder_collections
				WHERE form_id = {$new_form_id}
				  AND created_revision <= {$head}
				  AND (removed_revision IS NULL OR removed_revision > {$head})
			");
		}

		// Determines if the collection exists in the new table, if so an id is added
		// so the save function can add a removed revision
		foreach ($existing_collections as &$c) {
			foreach ($collections as &$collection) {
				if ($collection['name'] === $c['NAME']) {
					if ($collection['query'] === $c['QUERY']) {
						$collection['id'] = $c['ID'];
					} else {
						continue 2;
					}
				}
			}
		}
		unset($c, $collection);
	}

	// dd($collections);
	$actions = [];

	$revision_id = $form->save($models, $collections, $actions, $focus_user, $form_record['UPDATED_AT'], $old_new);
	$form->setHeadRevision($revision_id)->persist(false, false);

	// All form data migrated, now we'll flag it complete
	Database::query("UPDATE sss_forms SET new_form_id = {$new_form_id} WHERE id = {$old_form_id}");
	Database::commit();

	if (Database::$type === "postgres" && count($instance_records) > 3) {
		Database::query("VACUUM FULL formbuilder_components");
		Database::query("VACUUM FULL formbuilder_data");
		Database::query("VACUUM FULL formbuilder_instances");
		Database::query("VACUUM FULL formbuilder_objects");
	}
}

$sss_enabled = defined('SSS_ENABLED') && SSS_ENABLED === true;

Database::begin();

Database::query("
	UPDATE sss_form_instances SET
		form_id = new_form_id,
		instance_id = new_instance_id
	WHERE new_form_id IS NOT NULL
");

Database::query("ALTER TABLE sss_form_instances DROP COLUMN new_form_id");
Database::query("ALTER TABLE sss_form_instances DROP COLUMN new_instance_id");
Database::query("UPDATE sss_form_instances SET drafted = null WHERE draft_instance_id IS NULL");

$query = db_limit("SELECT f.new_form_id FROM sss_forms f WHERE f.id = gl_ap_approval_node.instance_id", 1);
Database::query("
	UPDATE gl_ap_approval_node SET instance_id = ({$query})
	WHERE instance_id IS NOT NULL
");

$query = db_limit("SELECT f.new_form_id FROM sss_forms f WHERE f.id = gl_ap_approval_substitute.instance_id", 1);
Database::query("
	UPDATE gl_ap_approval_substitute SET instance_id = ({$query})
	WHERE instance_id IS NOT NULL
");

$query = db_limit("SELECT f.new_form_id FROM sss_forms f WHERE f.id = gl_permission.instance_id", 1);
Database::query("
	UPDATE gl_permission SET instance_id = ({$query})
	WHERE instance_id IS NOT NULL
");

if ($sss_enabled) {
	$formTriggerConstraints = Database::getConstraints('sss_form_triggers');
	$bindingConstraints     = Database::getConstraints('sss_form_bindings');

	if (isset($formTriggerConstraints['sss_form_triggers_form_field_id_foreign'])) {
		Database::query("ALTER TABLE sss_form_triggers DROP CONSTRAINT sss_form_triggers_form_field_id_foreign");
	}

	if (isset($formTriggerConstraints['sss_form_triggers_form_id_foreign'])) {
		Database::query("ALTER TABLE sss_form_triggers DROP CONSTRAINT sss_form_triggers_form_id_foreign");
	}

	if (isset($bindingConstraints['sss_form_bindings_form_id_foreign'])) {
		Database::query("ALTER TABLE sss_form_bindings DROP CONSTRAINT sss_form_bindings_form_id_foreign");
	}

	// Some bindings are safe to delete because the form was never used on an event
	// and it was deleted. OR the form was used but the events were deleted and so
	// was the form.
	$query = db_limit("
		SELECT 1 FROM sss_forms
		WHERE sss_forms.id = sss_form_bindings.form_id
			AND new_form_id IS NOT NULL
	", 1);
	Database::query("
		DELETE FROM sss_form_bindings WHERE NOT EXISTS({$query})
	");

	$query = db_limit("SELECT f.new_form_id FROM sss_forms f WHERE f.id = sss_form_bindings.form_id", 1);
	Database::query("UPDATE sss_form_bindings SET form_id = ({$query})");

	Database::query("ALTER TABLE sss_form_bindings ADD CONSTRAINT sss_form_bindings_form_id_foreign FOREIGN KEY (form_id) REFERENCES formbuilder_forms(id);");

	Database::query("
		DELETE FROM sss_form_triggers WHERE NOT EXISTS(
			SELECT 1 FROM sss_forms
			WHERE sss_forms.id = sss_form_triggers.form_id
			AND new_form_id IS NOT NULL
		)
	");

	$query = db_limit("SELECT f.new_form_id FROM sss_forms f WHERE f.id = sss_form_triggers.form_id", 1);
	Database::query("UPDATE sss_form_triggers SET form_id = ({$query})");

	Database::query("ALTER TABLE sss_form_triggers ADD CONSTRAINT sss_form_triggers_form_id_foreign FOREIGN KEY (form_id) REFERENCES formbuilder_forms(id);");
	Database::query("ALTER TABLE sss_form_triggers DROP COLUMN form_field_id");
}

$query = db_limit("SELECT f.new_form_id FROM sss_forms f WHERE f.id = gl_requests.parent_id", 1);
Database::query("UPDATE gl_requests SET parent_id = ({$query})");

// We can delete dummy sss records now that instances are stored separate from sss
Database::query("UPDATE gl_requests SET instance_id = (SELECT instance_id FROM formbuilder_requests WHERE gl_requests.id = formbuilder_requests.request_id)");
Database::query("UPDATE gl_requests SET instance_id = (SELECT instance_id FROM sss_form_instances WHERE sss_form_instances.id = gl_requests.instance_id)");
Database::query("DELETE FROM sss_form_instances WHERE id IN (SELECT instance_id FROM formbuilder_requests)");


if(Database::tableExists('formbuilder_requests')){
	Database::query("DROP TABLE formbuilder_requests{$cascade}");
}

if (Database::tableExists('sss_form_field_instances')) {
	Database::query("DROP TABLE sss_form_field_instances{$cascade}");
}

if (Database::tableExists('sss_form_fields')) {
	Database::query("DROP TABLE sss_form_fields{$cascade}");
}

if (Database::tableExists('sss_form_collections')) {
	Database::query("DROP TABLE sss_form_collections{$cascade}");
}

if (Database::tableExists('formbuilder_join_profiles')) {
	Database::query("DROP TABLE formbuilder_join_profiles{$cascade}");
}


Database::query("ALTER TABLE sss_form_instances DROP COLUMN raw_data");
Database::query("ALTER TABLE sss_forms DROP COLUMN layout");

// mssql thinks default values for a column is somehow a show stopper for dropping the column
if (Database::$type === "mssql") {
	$constraints = Database::get("
		SELECT dc.Name FROM sys.tables t
		INNER JOIN sys.default_constraints dc ON t.object_id = dc.parent_object_id
		INNER JOIN sys.columns c ON dc.parent_object_id = c.object_id AND c.column_id = dc.parent_column_id
		WHERE t.Name = 'sss_forms' AND (c.name = 'language_id' OR c.name = 'folder')
	");

	$constraints = array_column($constraints, 'NAME');
	foreach ($constraints as $constraint) {
		Database::query("ALTER TABLE sss_forms DROP CONSTRAINT {$constraint}");
	}

	Database::query("DROP INDEX sss_forms_language_id_ind ON sss_forms"); // I don't like mssql, btw

	$foreigns = Database::getForeignKeys('sss_forms');
	foreach ($foreigns as $foreign) {
		$table = $foreign['LOCAL_TABLE'];

		if ($table === "formbuilder_join_tags" || $table === "formbuilder_join_profiles") {
			$constraint = $foreign['CONSTRAINT_NAME'];
			Database::query("ALTER TABLE {$table} DROP CONSTRAINT {$constraint}");
			break;
		}
	}
}

Database::query("ALTER TABLE sss_forms DROP COLUMN folder");
Database::query("ALTER TABLE sss_forms DROP COLUMN language_id");
Database::query("ALTER TABLE sss_forms DROP COLUMN name");

$formConstraints = Database::getConstraints('formbuilder_join_tags');
if (isset($formConstraints['formbuilder_join_tags_form_id_fkey'])) {
	Database::query("ALTER TABLE formbuilder_join_tags DROP CONSTRAINT formbuilder_join_tags_form_id_fkey");
}

Database::query("
	UPDATE formbuilder_join_tags SET form_id = sf.new_form_id
	FROM  sss_forms sf
	WHERE form_id = sf.id;
");
Database::query("ALTER TABLE formbuilder_join_tags ADD CONSTRAINT formbuilder_join_tags_form_id_fkey FOREIGN KEY(form_id) REFERENCES formbuilder_forms(id);");

// Update permissions so forms that showed up in menu before branch still shows up
$records = Database::get("
	SELECT f.id, f.new_form_id, p.id AS p_id, p.profile_id
	FROM sss_forms f
	INNER JOIN permission p ON (
		p.\"key\" LIKE CONCAT('form-builder/requests&form_id=', f.id, ':%')
		OR p.\"key\" LIKE CONCAT('form-builder/requests&form_id=', f.id, '&%')
	)
");

foreach ($records as $record) {
	$permission_id = $record['P_ID'];
	$old_form_id   = $record['ID'];
	$new_form_id   = $record['NEW_FORM_ID'];
	$profile_id    = $record['PROFILE_ID'];

	Database::query("UPDATE permission SET \"key\" = REPLACE(\"key\", '{$old_form_id}', '{$new_form_id}') WHERE id = {$permission_id} AND NOT EXISTS(SELECT 1 FROM permission WHERE \"key\" = REPLACE(\"key\", '{$old_form_id}', '{$new_form_id}') AND profile_id = {$profile_id})");
}

Database::commit();

// Reclaim space now that raw_data is outta here
if (Database::$type === "postgres") {
	Database::query("VACUUM FULL sss_form_instances");
	Database::query("VACUUM FULL sss_forms");
}

$_SESSION['do_sql_log'] = $do_sql_log;
