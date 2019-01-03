<?php
Migrations::depend("FOCUS-13538");

if (Database::$type !== "mssql") {
	return true;
}

Database::begin();

$sql  =
	"SELECT
		NEXT VALUE FOR \"exception_log_seq\" AS next";
$res  = Database::get($sql);
$next = (int) $res[0]["NEXT"];

if ($next < 0) {
	$sql =
		"ALTER SEQUENCE
			exception_log_seq
		RESTART WITH
			1";

	Database::query($sql);
}

Database::commit();
return true;
?>