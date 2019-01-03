<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"])
	return false;

$webroot = $_SERVER["DOCUMENT_ROOT"];
$ua      = $GLOBALS["uploaded_assets_dir"];

if (!is_dir("{$webroot}/logos"))
	@mkdir("{$webroot}/logos", 0755);

foreach (glob("{$ua}/????_check_logo.jpg") as $file)
	if (@rename($file, "{$webroot}/logos/" . basename($file)) === false)
		throw new Exception("Unable to move " . basename($file) . " into logos directory.");

foreach (glob("{$webroot}/logos/????_check_logo.jpg") as $file)
	if (@chmod($file, 0644) === false)
		throw new Exception("Unable to set proper permissings on " . basename($file) . ".");

return true;
