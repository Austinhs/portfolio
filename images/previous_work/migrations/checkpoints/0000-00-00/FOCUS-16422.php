<?php

// Tags: SSS
if (!defined('SSS_ENABLED') || SSS_ENABLED !== true) {
  return false;
}

Database::begin();

if (!Database::columnExists('sss_event_instances', 'prelock_instance')) {
  Database::createColumn('sss_event_instances', 'prelock_instance', 'BIGINT');
  Database::query("ALTER TABLE sss_event_instances ADD CONSTRAINT sss_event_instances_prelock_instance_foreign FOREIGN KEY (prelock_instance) REFERENCES formbuilder_instances(id)");
}

// Convert any forms saved previously to store the form instance id on sss_event_instances.prelock_instance column
$sss_instances = Database::get("SELECT id, table_id, \"value\" FROM sss_data WHERE \"table\" = 'sss_event_instances' AND \"key\" = 'rationale_form_instance_id' AND EXISTS(SELECT 1 FROM sss_event_instances WHERE id = sss_data.table_id)");
foreach ($sss_instances as $r) {
  $eventInstanceId = $r['TABLE_ID'];
  $sssFormInstance = $r['VALUE'];

  // Get form instance id from sss instance id
  $formInstance = Database::get("SELECT COALESCE(draft_instance_id, instance_id) AS instance_id FROM sss_form_instances WHERE id = {$sssFormInstance}");
  $formInstance = $formInstance[0]['instance_id'];

  // Move to new column which can have a foreign key for data integrity
  Database::query("UPDATE sss_event_instances SET prelock_instance = {$formInstance} WHERE id = {$eventInstanceId}");
}

// Remove old data
Database::query("DELETE FROM sss_data WHERE \"table\" = 'sss_event_instances' AND \"key\" = 'rationale_form_instance_id'");

Database::commit();