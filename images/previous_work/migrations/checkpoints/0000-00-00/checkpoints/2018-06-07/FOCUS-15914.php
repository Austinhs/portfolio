<?php

$objects = Database::get("select o.id, o.object from formbuilder_objects o where CAST(o.object AS varchar) like '%\"php\":%' and exists(select 1 from formbuilder_components c where c.model_id = o.id and c.type = 'form')");
foreach ($objects as $object) {
	$object_id = $object['ID'];
	$object    = json_decode($object['OBJECT'], true);

	if (key_exists('php', $object)) {
		unset($object['php']);

		KSortRecursive($object);
		$object = json_encode($object);
		$hash   = sha1($object);

		$id = Database::get("SELECT id FROM formbuilder_objects WHERE hash = :hash", compact('hash'));
		if (!empty($id)) {
			$id = $id[0]['ID'];
			Database::query("UPDATE formbuilder_components SET model_id = {$id} WHERE model_id = {$object_id}");
			Database::query("UPDATE formbuilder_components SET options_id = {$id} WHERE options_id = {$object_id}");
			Database::query("UPDATE formbuilder_components SET layout_id = {$id} WHERE layout_id = {$object_id}");
			Database::query("DELETE FROM formbuilder_objects WHERE id = {$object_id}");
		} else {
			Database::query("UPDATE formbuilder_objects SET object = :object, hash = :hash WHERE id = :id", [
				'id'     => $object_id,
				'object' => $object,
				'hash'   => $hash
			]);
		}
	}
}
