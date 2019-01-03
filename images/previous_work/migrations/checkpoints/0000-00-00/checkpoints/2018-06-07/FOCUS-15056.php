<?php

if (!Database::indexExists('sss_form_instances', 'sss_form_instances_instance_id_ind')) {
	Database::query("CREATE INDEX sss_form_instances_instance_id_ind ON sss_form_instances(instance_id)");
}

if (!Database::indexExists('sss_form_instances', 'sss_form_instances_draft_instance_id_ind')) {
	Database::query("CREATE INDEX sss_form_instances_draft_instance_id_ind ON sss_form_instances(draft_instance_id)");
}

Database::query(Database::preprocess("UPDATE formbuilder_forms SET name = {{trim:name}}"));
