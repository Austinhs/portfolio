<?php
$output = [];
if (!Database::columnExists('ps_fa_worksheets', 'resources_other2')) {
	Database::createColumn('ps_fa_worksheets', 'resources_other2', 'NUMERIC', '(10,2)', true);
	$output[] = "Add additional financial aid resource 'other2' for the FAWorksheet.";
}

if (!empty($output)) {
	echo implode("\n", $output);
}
