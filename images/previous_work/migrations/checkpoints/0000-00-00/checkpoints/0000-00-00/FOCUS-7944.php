<?php

if($GLOBALS['FocusFinanceConfig']['enabled']) {
	Database::query("UPDATE gl_dealer SET e_pay = 'Z' WHERE e_pay = 'S'");
	Database::query("UPDATE gl_dealer SET e_pay = 'Y' WHERE e_pay = 'C'");
}
else {
 return false;
}
