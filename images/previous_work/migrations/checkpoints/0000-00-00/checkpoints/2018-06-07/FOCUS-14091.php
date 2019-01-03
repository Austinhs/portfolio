<?php

if (!Database::columnExists('test_history_score_ranges', 'legacy')) {
	Database::createColumn('test_history_score_ranges', 'legacy', 'int');
	Database::query("UPDATE test_history_score_ranges SET legacy = 1");
}
