<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

if(!Database::tableExists(JournalSourceDefinition::$table)) {
	$GLOBALS['InstallingFinance'] = true;
	MetaData::importMetaData();
	$GLOBALS['InstallingFinance'] = false;
}

// This generates warnings, so I'm going to temporarily ignore them
$level = error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

$source_count = JournalSourceDefinition::getSourceCount();
if($source_count == 0) {
	JournalSourceDefinition::regenerate();
} else {
	JournalSourceDefinition::updateColumns(true);
}

error_reporting($level);
