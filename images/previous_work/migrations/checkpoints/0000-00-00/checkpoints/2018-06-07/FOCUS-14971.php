<?php

// Tags: Formbuilder
Database::query("UPDATE sss_form_instances SET instance_id = null WHERE instance_id IS NOT NULL AND instance_id NOT IN (SELECT formbuilder_instances.id FROM formbuilder_instances WHERE formbuilder_instances.id = sss_form_instances.instance_id)");
Database::query("UPDATE sss_form_instances SET draft_instance_id = null WHERE draft_instance_id IS NOT NULL AND draft_instance_id NOT IN (SELECT formbuilder_instances.id FROM formbuilder_instances WHERE formbuilder_instances.id = sss_form_instances.draft_instance_id)");

$constraints = Database::getConstraints('sss_form_instances');
if (isset($constraints['sss_form_instances_instance_id_foreign'])) {
	Database::query("ALTER TABLE sss_form_instances DROP CONSTRAINT sss_form_instances_instance_id_foreign");
}

if (isset($constraints['sss_form_instances_draft_instance_id_foreign'])) {
	Database::query("ALTER TABLE sss_form_instances DROP CONSTRAINT sss_form_instances_draft_instance_id_foreign");
}

Database::query("ALTER TABLE sss_form_instances ADD CONSTRAINT sss_form_instances_instance_id_foreign FOREIGN KEY (instance_id) REFERENCES formbuilder_instances(id)");
Database::query("ALTER TABLE sss_form_instances ADD CONSTRAINT sss_form_instances_draft_instance_id_foreign FOREIGN KEY (draft_instance_id) REFERENCES formbuilder_instances(id)");
