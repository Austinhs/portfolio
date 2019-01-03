<?php
if (Database::tableExists("test_history_scores")) {
	Database::query("
		DELETE FROM 
			test_history_scores 
		WHERE 
			test_code IS NULL 
			AND score IS NULL 
			AND EXISTS (
				SELECT 
					'' 
				FROM 
					test_history_administrations tha 
				WHERE 
					tha.id = test_history_scores.administration_id 
					AND tha.syear >= 2018
			)
	");
}
