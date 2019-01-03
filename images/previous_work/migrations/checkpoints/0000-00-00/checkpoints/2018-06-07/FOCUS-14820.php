<?php
if (Database::columnExists("custom_field_log_columns", "option_query")) {
	$sql = "
	UPDATE custom_field_log_columns 
	SET option_query = 'SELECT  id,
	  CONCAT(CERT_NUMBER,'' - '',DESCRIPTION) AS label,
	  (CASE WHEN SYEAR={SYEAR} THEN 0 ELSE 1 END) AS inactive
	FROM  FLORIDA_INDUSTRY_CERTIFICATIONS 
	ORDER BY 
	 CERT_NUMBER ASC'
	where
	    column_name = 'LOG_FIELD6' 
	    AND title = 'Industry Certification Identifier'
	    ";

	Database::query($sql);
}