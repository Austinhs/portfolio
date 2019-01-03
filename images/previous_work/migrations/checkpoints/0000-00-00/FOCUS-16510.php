<?php
if(Database::tableExists('school_fields'))
{
	if (Database::$type=='postgres') {
		Database::query("UPDATE importer_keys
			SET primary_keys = source.primary_keys
			FROM 
			(SELECT '[\"'||sfc.title||' > '||sf.title||' (custom_327)\"]' AS primary_keys
			FROM school_fields sf
			JOIN school_field_categories sfc ON sfc.id = sf.category_id
			WHERE sf.id = 327) source
			WHERE importer_keys.table_name = 'schools'
			");
	}
	else {
		Database::query("UPDATE importer_keys
			SET primary_keys = source.primary_keys
			FROM 
			(SELECT '[\"'+sfc.title+' > '+sf.title+' (custom_327)\"]' AS primary_keys
			FROM school_fields sf
			JOIN school_field_categories sfc ON sfc.id = sf.category_id
			WHERE sf.id = 327) source
			WHERE importer_keys.table_name = 'schools'");
	}
}