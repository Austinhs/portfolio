<?php
if (Database::$type == 'mssql') {
	Database::query("alter sequence referral_actions_seq restart with 1");
	Database::query("update referral_actions set id = next value for referral_actions_seq");
	Database::query("alter sequence referral_codes_seq restart with 1");
	Database::query("update referral_codes set id = next value for referral_codes_seq");
}