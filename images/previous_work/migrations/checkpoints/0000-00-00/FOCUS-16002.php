<?php

if(!Database::columnExists("students_join_users", "person_id")) {
	SISStudent::dropViews();
	Database::createColumn("students_join_users", "person_id", "BIGINT");
	SISStudent::refreshViews();
}
