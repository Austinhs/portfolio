<?php

if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

if(Settings::get('fa_default_disposition_assignment') == "asset_receiveed"){
    Settings::set('fa_default_disposition_assignment','asset_received');
}