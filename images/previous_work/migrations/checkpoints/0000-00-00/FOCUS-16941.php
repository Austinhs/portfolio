<?php	
$sql = "UPDATE 
			custom_fields 
		SET 
			visible_on_discipline_referral = NULL 

		WHERE  
			visible_on_discipline_referral is NOT NULL 
			
			AND 
			(
				TYPE = 'file' 
				OR TYPE = 'computed' 
				OR TYPE = 'computed_table'
			)";

Database::query($sql);