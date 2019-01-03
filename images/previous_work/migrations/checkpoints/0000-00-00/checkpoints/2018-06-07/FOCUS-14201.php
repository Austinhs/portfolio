<?php

Migrations::depend('FOCUS-15394');


if (!Database::columnExists("address_to_district", "former_zones")) {
	Database::createColumn("address_to_district", "former_zones", "TEXT");
}


if (!Database::indexExists("school_choice_application_status", "scas_syear")) {
	$sql =
		"CREATE INDEX
			scas_syear
		ON
			school_choice_application_status (syear)";

	Database::query($sql);
}

if (!Database::indexExists("school_choice_application_status", "scas_submitted_by")) {
	$sql =
		"CREATE INDEX
			scas_submitted_by
		ON
			school_choice_application_status (submitted_by)";

	Database::query($sql);
}

if (!Database::indexExists("school_choice_program_continuities", "scpc_from_program_id")) {
	$sql =
		"CREATE INDEX
			scpc_from_program_id
		ON
			school_choice_program_continuities (from_program_id)";

	Database::query($sql);
}

if (!Database::indexExists("school_choice_program_continuities", "scpc_to_program_id")) {
	$sql =
		"CREATE INDEX
			scpc_to_program_id
		ON
			school_choice_program_continuities (to_program_id)";

	Database::query($sql);
}

if (!Database::indexExists("school_choice_programs", "scp_school")) {
	$sql =
		"CREATE INDEX
			scp_school
		ON
			school_choice_programs (school)";

	Database::query($sql);
}

if (!Database::indexExists("school_choice_applications", "sca_verify")) {
	$sql =
		"CREATE INDEX
			sca_verify
		ON
			school_choice_applications (verify)";

	Database::query($sql);
}
