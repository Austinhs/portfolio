<?php 

Database::begin();

if (!Database::columnExists('schedule', 'functioning_level_initial_score_id')) {
	Database::createColumn('schedule', 'functioning_level_initial_score_id', 'BIGINT', false, true);
}
if (!Database::columnExists('schedule', 'functioning_level_final_score_id')) {
	Database::createColumn('schedule', 'functioning_level_final_score_id', 'BIGINT', false, true);
}

Database::commit();
