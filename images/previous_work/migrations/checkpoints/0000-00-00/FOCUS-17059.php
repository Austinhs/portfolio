<?php
Database::begin();

Database::query("
	WITH data_tbl AS (
		SELECT
			sjp.id
		,	sjp.student_id
		,	sjp.sort_order
		,	DENSE_RANK() OVER (
				PARTITION BY sjp.student_id
				ORDER BY
					sjp.sort_order,
					CASE WHEN COALESCE(sjp.custody, 'N') = 'Y' THEN 1 ELSE 99 END ASC
					,	CASE WHEN COALESCE(sjp.emergency, 'N') = 'Y' THEN 1 ELSE 99 END ASC
					,	CASE WHEN COALESCE(sja.residence, 'N') = 'Y' THEN 1 ELSE 99 END ASC
					,	sjp.student_relation
					,	p.last_name
					,	p.first_name
					,	sjp.person_id ASC
			) AS generated_sort_order
		,	sjp.custody
		,	sjp.address_id
		,	sjp.student_relation
		,	sjp.emergency
		,	sjp.person_id
		FROM
			students_join_people sjp
			JOIN students s ON sjp.student_id = s.student_id
			JOIN people p ON sjp.person_id = p.person_id
			LEFT OUTER JOIN students_join_address sja ON sja.address_id = sjp.address_id AND sja.student_id = s.student_id
		WHERE
			1=1
			AND COALESCE(sjp.deleted, 0) = 0
	)
	UPDATE
		students_join_people
	SET
		sort_order = dt.generated_sort_order
	FROM
		data_tbl dt
	WHERE
		1=1
		AND students_join_people.id = dt.id
		AND students_join_people.student_id = dt.student_id
		AND students_join_people.person_id = dt.person_id
	;"
);

Database::query("
	WITH dupes AS (
		SELECT
			sjp.student_id
		,	sjp.person_id
		,	sjp.address_id
		,	COUNT(sjp.sort_order) AS cnt
		FROM
			students_join_people sjp
		WHERE
			1=1
			AND COALESCE(sjp.deleted, 0) = 0
			AND sjp.sort_order IS NOT NULL
		GROUP BY
			sjp.student_id, sjp.person_id, sjp.address_id
		HAVING
			COUNT(sjp.sort_order) > 1
	)

	, del_recs AS (
		SELECT
			sjp.student_id
		,	sjp.person_id
		,	sjp.address_id
		,	MIN(sjp.id) AS delete_ids
		FROM
			students_join_people sjp
			JOIN dupes d
				ON sjp.student_id = d.student_id
				AND sjp.person_id = d.person_id
				AND sjp.address_id = d.address_id
		GROUP BY
			sjp.student_id, sjp.person_id, sjp.address_id
	)

	DELETE FROM
		students_join_people
	WHERE
		id IN (SELECT dr.delete_ids FROM del_recs dr)
	;"
);

Database::commit();