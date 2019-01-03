<?php

Database::begin();

Database::query(
	"DELETE FROM PROGRAM_USER_CONFIG
	WHERE PROGRAM = 'StudentFieldsView' AND TITLE IN (SELECT CAST(ID as varchar) FROM CUSTOM_FIELDS WHERE SOURCE_CLASS = 'SISStudent' AND TYPE='signature')"
);

Database::commit();