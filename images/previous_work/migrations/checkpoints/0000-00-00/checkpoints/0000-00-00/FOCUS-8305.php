<?php
// Tags: Formbuilder
Migrations::depend('FOCUS-9271'); // creates sss_form_instances
Migrations::depend('FOCUS-10647'); // creates gl_requests

if (!Database::tableExists('formbuilder_requests')) {
	Database::createSequence('formbuilder_requests_seq');

	$sql = Database::preprocess("
		CREATE TABLE formbuilder_requests (
			id BIGINT PRIMARY KEY DEFAULT {{next:formbuilder_requests_seq}},
			request_id BIGINT REFERENCES gl_requests(id) ON DELETE CASCADE,
			instance_id BIGINT REFERENCES sss_form_instances(id) ON DELETE CASCADE
		)
	");

	Database::query($sql);
}
