<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (Database::tableExists("gl_pr_tx_er10"))
	Database::query("drop table gl_pr_tx_er10");

if (Database::tableExists("gl_pr_tx_er20"))
	Database::query("drop table gl_pr_tx_er20");

if (Database::tableExists("gl_pr_tx_er25"))
	Database::query("drop table gl_pr_tx_er25");

if (Database::tableExists("gl_pr_tx_er27"))
	Database::query("drop table gl_pr_tx_er27");

if (Database::tableExists("gl_pr_tx_er20_results"))
	Database::query("drop table gl_pr_tx_er20_results");

if (Database::tableExists("GL_PR_TX_MD20"))
	Database::query("drop table GL_PR_TX_MD20");

if (Database::tableExists("GL_PR_TX_MD25"))
	Database::query("drop table GL_PR_TX_MD25");

if (Database::tableExists("GL_PR_TX_MD30"))
	Database::query("drop table GL_PR_TX_MD30");

if (Database::tableExists("GL_PR_TX_MD40"))
	Database::query("drop table GL_PR_TX_MD40");

if (Database::tableExists("GL_PR_TX_MD45"))
	Database::query("drop table GL_PR_TX_MD45");

if (Database::tableExists("GL_PR_TX_MD90"))
	Database::query("drop table GL_PR_TX_MD90");

if (Database::tableExists("GL_PR_TX_MD90_OVERRIDE"))
	Database::query("drop table GL_PR_TX_MD90_OVERRIDE");

if (Database::tableExists("GL_PR_TX_RP10_ADJUSTMENTS"))
	Database::query("drop table GL_PR_TX_RP10_ADJUSTMENTS");

if (Database::tableExists("GL_PR_TX_RP15"))
	Database::query("drop table GL_PR_TX_RP15");

if (Database::tableExists("GL_PR_TX_RP20"))
	Database::query("drop table GL_PR_TX_RP20");

if (Database::tableExists("GL_PR_TX_RP20_ADJUSTMENTS"))
	Database::query("drop table GL_PR_TX_RP20_ADJUSTMENTS");

if (Database::tableExists("GL_PR_TX_RP25"))
	Database::query("drop table GL_PR_TX_RP25");

if (Database::tableExists("GL_PR_TX_RP20_PREV_MONTH_CHANGES"))
	Database::query("drop table GL_PR_TX_RP20_PREV_MONTH_CHANGES");

if (Database::tableExists("GL_PR_TX_RP20_PREV_MONTH_CHANGES_BK_REMOVE_DUP_STRIPS"))
	Database::query("drop table GL_PR_TX_RP20_PREV_MONTH_CHANGES_BK_REMOVE_DUP_STRIPS");

Database::commit();
return true;
?>






