<?php

if($GLOBALS['FocusFinanceConfig']['enabled'])
	Database::query('update gl_element_category set updated_at = current_timestamp');
else
	return false;
