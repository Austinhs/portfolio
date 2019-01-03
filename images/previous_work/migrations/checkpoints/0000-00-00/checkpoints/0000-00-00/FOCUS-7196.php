<?php
$output = [];
if (Database::tableExists('ps_fa_saig_files')) {
	if (!Database::columnExists('ps_fa_saig_files', 'award_year')) {
		Database::createColumn('ps_fa_saig_files', 'award_year', 'numeric', '4', true);
	}
}

$files = SAIGPortalFile::getAllAndLoad();
if (!empty($files)) {
	$output[] = "Update previously imported SAIG files. Setting award year on ps_fa_saig_files table.";
}
foreach ($files as $id => $file) {
	$awardYear = $file->getAwardYear();
	$file->setAwardYear($awardYear);
	$file->persist();
}

if (!empty($output)) {
	echo implode(PHP_EOL, $output);
}
