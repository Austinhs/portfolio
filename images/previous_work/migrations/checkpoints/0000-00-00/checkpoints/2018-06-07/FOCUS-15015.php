<?php

// Tags: Formbuilder
$constraints = Database::getConstraints('gl_requests');
if (!isset($constraints['gl_request_instance_id_foreign'])) {
	Database::query("ALTER TABLE gl_requests DROP CONSTRAINT IF EXISTS gl_request_instance_id_foreign");
}

if (!isset($constraints['gl_requests_instance_id_foreign'])) {
	Database::query("ALTER TABLE gl_requests DROP CONSTRAINT IF EXISTS gl_requests_instance_id_foreign");
}

Database::query("ALTER TABLE gl_requests ADD CONSTRAINT gl_requests_instance_id_foreign FOREIGN KEY (instance_id) REFERENCES formbuilder_instances(id)");
