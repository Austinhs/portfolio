<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::columnExists("gl_element_use_restrictions", "display_type")) {
	Database::createColumn("gl_element_use_restrictions", "display_type", "char(1)");
}

if (!Database::columnExists("gl_pr_deductions", "object_mask")) {
	Database::createColumn("gl_pr_deductions", "object_mask", "varchar(20)");

	// Database::query("
	// 	update gl_pr_deductions
	// 	set object_mask =
	// 	(
	// 		select e.code
	// 		from gl_pr_deductions d
	// 		join gl_element e on e.id = d.object_id
	// 		and d.id = gl_pr_deductions.id
	// 	)
	// 	where object_mask is null
	// 	and object_id is not null
	// ");

}

Database::commit();
return true;
?>
