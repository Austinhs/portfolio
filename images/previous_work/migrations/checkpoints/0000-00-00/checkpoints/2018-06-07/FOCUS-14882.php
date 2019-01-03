<?php
// Block run of MS SQL Server code for now.
if (Database::$type === 'mssql') {
	return false;
}

// Make sure FOCUS-14161 has run so that changes can be made.
Migrations::depend('FOCUS-14161');

// Remove immunization uninstall function.
Database::query("drop function if exists fn_imm_uninstall();");
