<?php

if(!Database::columnExists("test_history_parts", "transcript")) {
	$sql = "UPDATE test_history_parts SET transcript = 'Y'";
	Database::createColumn("test_history_parts", "transcript", "varchar", "1");
	Database::query($sql);
}

if(Database::columnExists("test_history_tests", "post_secondary")) {
	Database::changeColumnType("test_history_tests", "post_secondary", "varchar");
	$sql = "UPDATE test_history_tests SET post_secondary = 'all_admin' WHERE post_secondary = 'Y'";
	Database::query($sql);
}

if(Database::columnExists("test_history_tests", 'inter_district')) {
	Database::changeColumnType("test_history_tests", "inter_district", "varchar");
	$sql = "UPDATE test_history_tests SET inter_district = 'all_admin' WHERE inter_district = 'Y'";
	Database::query($sql);
}