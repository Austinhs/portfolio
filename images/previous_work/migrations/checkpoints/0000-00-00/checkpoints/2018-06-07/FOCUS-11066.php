<?php

if(Database::$type === 'postgres') {
	$query = "UPDATE program_user_config set value=REGEXP_REPLACE(value,'\&students\[\d+\]=Y','','g') where program='ReportCards' and title like 'LinkFor%' and value like '%&students%=Y&%'";
	Database::query($query);
}
else if(Database::$type === 'mssql') {
	$report_card_reports = Database::query("SELECT * from program_user_config where program='ReportCards' and title like 'LinkFor%' and value like '%&students%=Y&%'");

	foreach ($report_card_reports as $report_card) {

		$updated_link = preg_replace('/\&students\[\d+\]=Y/m', '', $report_card['VALUE']);

		$insert[] = [
			'program'   => $report_card['PROGRAM'],
			'username'  => $report_card['USERNAME'],
			'title'     => $report_card['TITLE'],
			'syear'     => $report_card['SYEAR'],
			'school_id' => $report_card['SCHOOL_ID'],
			'value'     => $updated_link
		];
	}
	if(!empty($insert))
	{
		$columns = ['program', 'username', 'title', 'syear', 'school_id', 'value'];

		if(!Database::tableExists('reportcards_pug_temp')) {
			//create temp table
			Database::query("
				CREATE table reportcards_pug_temp (
					id bigint primary key,
					program varchar(255) null,
					username varchar(100),
					title varchar(100),
					syear numeric(4) null,
					school_id numeric(18) null,
					value text
				)
			");
		}
		if(!Database::sequenceExists('reportcards_pug_temp_seq')) {
			Database::createSequence('reportcards_pug_temp_seq');
		}

		Database::insert('reportcards_pug_temp', 'reportcards_pug_temp_seq', $columns, $insert);

		Database::query("
				UPDATE
					program_user_config
				SET
					value = rpt.value
				FROM
					reportcards_pug_temp rpt
				WHERE
					program_user_config.program=rpt.program and
					program_user_config.username=rpt.username and
					program_user_config.title=rpt.title and
					program_user_config.syear=rpt.syear and
					program_user_config.school_id=rpt.school_id and
					program_user_config.program='ReportCards' and
					program_user_config.title like 'LinkFor%' and
					program_user_config.value like '%&students%=Y&%'
				");

		Database::query("DROP table reportcards_pug_temp");
		Database::dropSequence('reportcards_pug_temp_seq');
	}
}
