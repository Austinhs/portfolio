<?php
if(Database::tableExists('gl_pr_staff_job_positions'))
{
	if(!Database::columnExists("gl_pr_staff_job_positions", "temp_position_code ")) {
		Database::createColumn("gl_pr_staff_job_positions", "temp_position_code ", "varchar", "100");
	}
}
?>