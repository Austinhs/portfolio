<?php

if (empty($GLOBALS['FocusFinanceConfig']['enabled'])) {
	return false;
}

if (!Database::tableExists("gl_facilities_by_year")) {
	Database::query("
		CREATE TABLE gl_facilities_by_year (
			id BIGINT PRIMARY KEY,
			deleted BIGINT,
			facility_id BIGINT NOT NULL,
			fyear BIGINT NOT NULL,
			survey_5_calendar_id BIGINT
		)
	");
}
