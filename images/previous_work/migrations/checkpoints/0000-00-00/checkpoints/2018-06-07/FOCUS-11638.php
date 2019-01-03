<?php
Database::query("
	UPDATE
		program_config
	SET
		TITLE = 'GRADEBOOK_METHOD'

	WHERE
		TITLE = 'SEM_GRADEBOOK' AND
		PROGRAM IN ('Gradebook_Options_dist','Gradebook_Options') AND
		NOT EXISTS (
			SELECT
				''
			FROM
				program_config pc
			WHERE
				(program_config.syear=pc.syear or coalesce(program_config.syear,pc.syear) is null)
				AND (program_config.school_id=pc.school_id or coalesce(program_config.school_id,pc.school_id) is null)
				AND program_config.program=pc.program
				AND pc.title='GRADEBOOK_METHOD'
		)
");

