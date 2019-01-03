<?php

if (!CTE_enabled()) {
	return false;
}

if (Database::tableExists('demographic_report_queries')) {
	$id_sql = "SELECT
				drq.id
			FROM
				custom_field_categories cfc
			INNER JOIN demographic_report_queries drq
				ON drq.category_id = cfc.id
			WHERE
				cfc.id = 6
				AND cfc.title = 'Financial Aid'
				AND drq.id = 23";

	$demo_query = "DELETE FROM demographic_report_queries WHERE id = ($id_sql)";

	Database::query($demo_query);
}