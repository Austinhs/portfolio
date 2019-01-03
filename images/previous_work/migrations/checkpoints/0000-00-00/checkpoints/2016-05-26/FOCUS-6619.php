<?php

$order_sql = "
	SELECT
		cf.id,
		ROW_NUMBER() OVER (PARTITION BY MIN(cf.source_class) ORDER BY MIN(cfc.title) ASC, MIN(cfjc.sort_order) ASC, MIN(cf.title) ASC) AS sort_order
	FROM
		custom_fields cf JOIN
		custom_fields_join_categories cfjc ON
			cf.id = cfjc.field_id JOIN
		custom_field_categories cfc ON
			cfc.id = cfjc.category_id
	WHERE
		COALESCE(cf.deleted, cfjc.deleted, 0) = 0 AND
		cf.new_record IS NOT NULL
	GROUP BY
		cf.id
";

$update_sql = "
	WITH orders AS
		({$order_sql})
	UPDATE
		custom_fields
	SET
		new_record = (
			SELECT
				sort_order
			FROM
				orders
			WHERE
				id = custom_fields.id
		)
	WHERE
		COALESCE(deleted, 0) = 0 AND
		new_record IS NOT NULL
";

Database::query($update_sql);
