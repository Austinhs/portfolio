<?php
Database::begin();
if (Database::$type == 'mssql') {
	Database::query("UPDATE DISCIPLINE_REFERRALS_FIELDS SET SELECT_OPTIONS = REPLACE(cast(SELECT_OPTIONS as varchar(max)),'`','''')");
} else {
	Database::query("UPDATE DISCIPLINE_REFERRALS_FIELDS SET SELECT_OPTIONS = REPLACE(SELECT_OPTIONS,'`','''')");
}
Database::commit();