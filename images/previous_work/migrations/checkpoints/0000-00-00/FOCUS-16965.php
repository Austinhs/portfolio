<?php

if(!Database::tableExists('external_api_permission')) {
	$query = "
		CREATE TABLE external_api_permission (
			id BIGINT PRIMARY KEY,
			external_api_id BIGINT,
			method VARCHAR(255),
			type VARCHAR(255)
		)
	";

	Database::query($query);
	Database::createSequence('external_api_permission_seq');

	$clients    = ExternalAPI::getAllAndLoad();
	$version    = '1.0';
	$api        = new API($version);
	$definition = $api->getDefinition('focus');

	$insert_records = [
		'GET' => [],
		'PUT' => [],
	];

	// Get valid get and put types
	$route_types = $api->getRouteTypes();

	$query = "
		SELECT
			external_api_id,
			result_schema
		FROM
			external_api_schema_restriction
	";

	$tmp_restrictions = Database::get($query);
	$restrictions     = [];

	// Index restrictions by id and type
	foreach($tmp_restrictions as $restriction) {
		$external_api_id = $restriction['EXTERNAL_API_ID'];
		$type            = $restriction['RESULT_SCHEMA'];

		if(!isset($restrictions[$external_api_id])) {
			$restrictions[$external_api_id] = [];
		}

		$restrictions[$external_api_id][$type] = true;
	}

	foreach($clients as $client) {
		$external_api_id = $client->getId();

		foreach($route_types as $method => $types) {
			foreach($types as $type => $foo) {
				if($method === 'PUT' && !in_array($type, ['assignment', 'grade'])) {
					continue;
				}

				// If they don't have permission to the route
				if(
					isset($restrictions[$external_api_id]) &&
					isset($restrictions[$external_api_id][$type])
				) {
					continue;
				}

				// At this point the new permission record can be created
				$insert_records[$method][] = [
					'external_api_id' => $external_api_id,
					'method'          => $method === 'GET' ? 'GET' : 'PUT',
					'type'            => $type,
				];
			}
		}
	}

	if(!empty($insert_records["GET"])) {
		Database::insert(ExternalAPIPermission::$table, ExternalAPIPermission::$sequence, array_keys($insert_records["GET"][0]), $insert_records["GET"]);
	}

	if(!empty($insert_records["PUT"])) {
		Database::insert(ExternalAPIPermission::$table, ExternalAPIPermission::$sequence, array_keys($insert_records["PUT"][0]), $insert_records["PUT"]);
	}
}