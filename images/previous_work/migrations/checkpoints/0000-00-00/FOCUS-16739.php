<?php

Migrations::depend("FOCUS-15981");

global $_SAML;

if ($_SAML["auth"] === true) {
	$preference_exists_sql = "
		SELECT
			value
		FROM
			program_config pc
		WHERE
			pc.title = 'USE_SAML_USER'
			AND pc.program = 'system'
	";

	$preference_exists = Database::get($preference_exists_sql);

	if (empty($preference_exists)) {
		$insert_sql = "
			INSERT INTO
				program_config
			VALUES (
				:syear,
				NULL,
				'system',
				'USE_SAML_USER',
				'Y'
			)
		";

		Database::query($insert_sql, ["syear" => getDefaultSyear()]);
	}
}
