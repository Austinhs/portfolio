<?php
if(Database::tableExists('gl_pr_retirement_adjustments'))
{
	if(!Database::columnExists('gl_pr_retirement_adjustments', 'send_month')) {
		Database::createColumn('gl_pr_retirement_adjustments', 'send_month', 'varchar(2)');
	}

	if(!Database::columnExists('gl_pr_retirement_adjustments', 'send_year')) {
		Database::createColumn('gl_pr_retirement_adjustments', 'send_year', 'varchar(4)');
	}
}

if(Database::tableExists('gl_pr_slot_pay_effective'))
{
	if(!Database::columnExists('gl_pr_slot_pay_effective','slot_seq')) {
		Database::createColumn('gl_pr_slot_pay_effective','slot_seq','varchar(20)');
	}
}