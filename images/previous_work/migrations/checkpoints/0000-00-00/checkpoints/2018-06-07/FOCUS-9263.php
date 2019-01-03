<?php
// Tags: Formbuilder
$tags    = Database::get("SELECT title FROM formbuilder_tags WHERE title IN ('ERP', 'SIS')");
$tags    = array_column($tags, 'TITLE');
$columns = ['title'];
$rows    = [];

if (!in_array('ERP', $tags)) {
	$rows[] = [ 'title' => 'ERP' ];
}

if (!in_array('SIS', $tags)) {
	$rows[] = [ 'title' => 'SIS' ];
}

if (!empty($rows)) {
	Database::insert('formbuilder_tags', null, $columns, $rows);
}
