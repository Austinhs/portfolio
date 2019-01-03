<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(!Database::indexExists('gl_pos_payment','gl_pos_payment_ind_voided_date'))
	Database::query("create index gl_pos_payment_ind_voided_date on gl_pos_payment (voided_date)");

if(!Database::indexExists('gl_pos_payment','gl_pos_payment_ind_source_class'))
	Database::query("create index gl_pos_payment_ind_source_class on gl_pos_payment (source_class)");

if(!Database::columnExists('gl_pr_leave_bucket_groups','status'))
	Database::createColumn('gl_pr_leave_bucket_groups','status','varchar',1);