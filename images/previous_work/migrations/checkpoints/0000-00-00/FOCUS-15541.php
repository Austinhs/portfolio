<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}



if (!Database::tableExists("gl_pr_personnel_evaluation")) {
	Database::query("drop table gl_pr_personnel_evaluation");
}



Database::commit();
return true;
?>
