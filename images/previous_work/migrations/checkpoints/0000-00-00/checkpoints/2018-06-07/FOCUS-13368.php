<?php
if(!Database::indexExists('standards','standards_ind5'))
	Database::query("create index standards_ind5 on standards (rollover_id)");

if(!Database::indexExists('school_periods','school_periods_ind2'))
	Database::query("create index school_periods_ind2 ON school_periods (rollover_id)");

if(!Database::indexExists('report_card_grade_scales','report_card_grade_scales_ind2'))
	Database::query("create index report_card_grade_scales_ind2 on report_card_grade_scales (syear,school_id,rollover_id)");

if(!Database::indexExists('GRADE_POSTING_TERM_WEIGHTS','GRADE_POSTING_TERM_WEIGHTS_ind2'))
	Database::query("create index GRADE_POSTING_TERM_WEIGHTS_ind2 on GRADE_POSTING_TERM_WEIGHTS (GRADE_POSTING_SCHEME_ID)");
