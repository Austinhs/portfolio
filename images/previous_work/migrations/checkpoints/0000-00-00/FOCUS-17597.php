<?php

// Tags: Formbuilder

// Remove form-level code because it's deprecated and often breaks forms
if (Database::$type == "mssql") {
	Database::query("
		UPDATE formbuilder_objects
			SET object = CONCAT('{\"title\": \"', (
					SELECT TOP 1 fbf.name
					FROM formbuilder_forms fbf
						JOIN formbuilder_components fbc ON fbf.id = fbc.form_id
						JOIN formbuilder_objects fbo ON fbc.model_id = fbo.id
					WHERE fbo.id = formbuilder_objects.id
				), '\"}')
		WHERE id IN (
			SELECT fbo.id
			FROM formbuilder_components fbc
				JOIN formbuilder_objects fbo ON fbo.id = fbc.model_id
				JOIN formbuilder_forms fbf ON fbc.form_id = fbf.id
			WHERE fbc.name = 'form'
				AND object LIKE '%formCode%'
		)
	");
}
else {
	Database::query("
		UPDATE formbuilder_objects
			SET object = json_build_object('title', (
					SELECT fbf.name
					FROM formbuilder_forms fbf
						JOIN formbuilder_components fbc ON fbf.id = fbc.form_id
						JOIN formbuilder_objects fbo ON fbc.model_id = fbo.id
					WHERE fbo.id = formbuilder_objects.id
					LIMIT 1
				)
			)::jsonb
		WHERE id IN (
			SELECT fbo.id
			FROM formbuilder_components fbc
				JOIN formbuilder_objects fbo ON fbo.id = fbc.model_id
				JOIN formbuilder_forms fbf ON fbc.form_id = fbf.id
			WHERE fbc.name = 'form'
				AND object::text LIKE '%formCode%'
		)
	");
}
