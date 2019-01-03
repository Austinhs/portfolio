<?php
$delete_ids_func = function($keep_id) {
	// skip inactive
	$delete_ids = Database::get("
	SELECT
		cfso.id AS id
	FROM
		custom_field_select_options cfso
		JOIN custom_field_log_columns cflc ON cfso.source_class = 'CustomFieldLogColumn'
		AND cfso.source_id = cflc.id
		JOIN custom_fields cf ON cf.id = cflc.field_id
		WHERE
		cfso.id NOT IN ({$keep_id})
		AND cf.source_class            = 'SISStudent'
		AND cf.alias                   = 'letter_log'
		AND cflc.title                 = 'Recipient'
		AND COALESCE(cfso.inactive, 0) = 0
		");
	return $delete_ids;
};

$keep_id_func = function($label) {
	$keep_id = Database::get("
	SELECT DISTINCT cfso.label,
		MIN(cfso.id) AS id,
		cf.id AS entry_id
	FROM
		custom_field_select_options cfso
		JOIN custom_field_log_columns cflc ON cfso.source_class = 'CustomFieldLogColumn'
		AND cfso.source_id             = cflc.id
		AND cfso.label                 = '{$label}'
		JOIN custom_fields cf ON cf.id = cflc.field_id
	WHERE
		cf.source_class                = 'SISStudent'
		AND cf.alias                   = 'letter_log'
		AND cflc.title                 = 'Recipient'
		AND COALESCE(cfso.deleted, 0)  = 0
		AND COALESCE(cfso.inactive, 0) = 0
	GROUP BY
		cfso.label,
		cf.id
		");

	return $keep_id;
};

// this query gets all ids and then check for duplicate labels
$keeper = Database::get("SELECT MIN(cfso.id) AS id,
	cflc.id AS column_id,
	cfso.label
FROM
	custom_field_select_options cfso
	JOIN custom_field_log_columns cflc ON cfso.source_class = 'CustomFieldLogColumn'
	AND cfso.source_id             = cflc.id
	JOIN custom_fields cf ON cf.id = cflc.field_id
WHERE
	cf.source_class               = 'SISStudent'
	AND cf.alias                  = 'letter_log'
	AND cflc.title                = 'Recipient'
	AND COALESCE(cfso.DELETED, 0) = 0
GROUP BY
	cflc.id,
	cfso.id,
	cfso.label;
");

$counts = array_count_values(array_column($keeper, 'LABEL'));

if (
	(isset($counts['Parents']) && $counts['Parents'] !== 1)||
	(isset($counts['Student']) && $counts['Student'] !== 1) ||
	(isset($counts['Both'])    && $counts['Both']    !== 1)) {

	if($counts['Parents'] > 1) {

		$keep_id      = $keep_id_func('Parents');
		$logentry_fid = $keep_id[0]['ENTRY_ID'];
		$keep_id      = $keep_id[0]['ID'];
		$delete_ids   = $delete_ids_func($keep_id);
		$delete_ids   = join("','", array_column($delete_ids, 'ID'));
		$delete_ids   = "'".$delete_ids."'";
	}

	if($counts['Student'] > 1) {

		$keep_id      = $keep_id_func('Student');
		$logentry_fid = $keep_id[0]['ENTRY_ID'];
		$keep_id      = $keep_id[0]['ID'];
		$delete_ids   = $delete_ids_func($keep_id);
		$delete_ids   = join("','", array_column($delete_ids, 'ID'));
		$delete_ids   = "'".$delete_ids."'";
	}
	if($counts['Both'] > 1) {

		$keep_id      = $keep_id_func('Parents');
		$logentry_fid = $keep_id[0]['ENTRY_ID'];
		$keep_id      = $keep_id[0]['ID'];
		$delete_ids   = $delete_ids_func($keep_id);
		$delete_ids   = join("','", array_column($delete_ids, 'ID'));
		$delete_ids   = "'".$delete_ids."'";
	}

// postgres will convert $delete_ids into intergers so they need to be surrounded by ''s
	Database::query("
		UPDATE
			custom_field_log_entries
		SET
			log_field1 = {$keep_id}
		WHERE
			log_field1 in ({$delete_ids})
			AND source_class = 'SISStudent'
			AND field_id = {$logentry_fid}
		");

// set duplicate ID's to be marked deleted
	if(Database::$type == 'postgres') {
		Database::query("
			UPDATE
				custom_field_select_options cfs
			SET
				deleted = 1
			FROM
				(
				SELECT
					MIN(cfso.id) AS id,
					cflc.id AS column_id,
					cfso.label
				FROM
					custom_field_select_options cfso
					JOIN custom_field_log_columns cflc ON cfso.source_class = 'CustomFieldLogColumn'
					AND cfso.source_id = cflc.id
					JOIN custom_fields cf ON cf.id = cflc.field_id
				WHERE
					cf.source_class = 'SISStudent'
					AND cf.alias = 'letter_log'
					AND cflc.title = 'Recipient'
				GROUP BY
					cflc.id,
					cfso.label
			) as keep
			WHERE
				cfs.source_class = 'CustomFieldLogColumn'
				AND cfs.source_id = keep.column_id
				AND cfs.label = keep.label
				AND cfs.id != keep.id
				AND COALESCE(cfs.inactive, 0) = 0
		");
	}
	else{
		Database::query("
			UPDATE
				cfs
			SET
				deleted = 1
			FROM
				custom_field_select_options cfs
				JOIN (
					SELECT
						MIN(cfso.id) AS id,
						cflc.id AS column_id,
						cfso.label
					FROM
						custom_field_select_options cfso
						JOIN custom_field_log_columns cflc ON cfso.source_class = 'CustomFieldLogColumn'
						AND cfso.source_id = cflc.id
						JOIN custom_fields cf ON cf.id = cflc.field_id
					WHERE
						cf.source_class = 'SISStudent'
						AND cf.alias = 'letter_log'
						AND cflc.title = 'Recipient'
					GROUP BY
						cflc.id,
						cfso.label
				) AS keep ON cfs.source_class = 'CustomFieldLogColumn'
				AND cfs.source_id = keep.column_id
				AND cfs.label = keep.label
				AND cfs.id != keep.id
				AND COALESCE(cfs.inactive, 0) = 0
		");
	}
}
