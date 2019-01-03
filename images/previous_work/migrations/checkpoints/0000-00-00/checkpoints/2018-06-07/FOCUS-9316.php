<?php
if(!Database::tableExists('gl_tx_element_crosswalk')) {
	Database::query(
	"CREATE TABLE gl_tx_element_crosswalk (
		id bigint,
		element_category_id bigint,
		low varchar(4),
		high varchar(4),
		crosswalk_to varchar(10)
	)");
}

if(!Database::tableExists('gl_tx_strip_crosswalk')) {
	Database::query(
		"CREATE TABLE gl_tx_strip_crosswalk (
		id bigint,
		fund varchar(4),
		func varchar(2),
		obj varchar(4),
		org varchar(3),
		pic varchar(4),
		element_category_id bigint,
		crosswalk_to varchar(3)
	)");
}
