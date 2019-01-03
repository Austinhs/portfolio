<?php

//RISD
if($ClientId === 20862)
{
	$RET = Database::get("SELECT 1 FROM program_config WHERE program='district' AND title='DISTRICT_NAME'");

	if(count($RET) === 0)
	{
		Database::query("INSERT INTO program_config(program, title, value) values('district', 'DISTRICT_NAME', 'Richardson Independent School District')");
	}
}
