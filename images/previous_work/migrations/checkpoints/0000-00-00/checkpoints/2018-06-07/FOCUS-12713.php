<?php

if(empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

$datetimeField = 'TIMESTAMP';

if(Database::$type === "mssql") {
	$datetimeField = 'DATETIME2';
}

Database::begin();

if(!Database::tableExists('gl_pos_invoice_print_audit_trail')) {
	$sql = "
		CREATE TABLE gl_pos_invoice_print_audit_trail (
			ID           BIGINT           PRIMARY KEY,
			NOTICE_ID    BIGINT           NULL,
			PRINTED_DATE {$datetimeField} NULL,
			INVOICE_ID   BIGINT           NULL,
			PRINTED_BY   BIGINT           NULL,
			BATCH        INT              NULL,
			DELETED      INT              NULL
		)
	";

	Database::query($sql);
}

if(!Database::tableExists('gl_pos_invoice_past_due_notice')) {
	$sql = "
		CREATE TABLE gl_pos_invoice_past_due_notice (
			ID               BIGINT           PRIMARY KEY,
			TIER             INT              NULL,
			CREATED_DATE     {$datetimeField} NULL,
			INVOICE_ID       BIGINT           NULL,
			CREATED_BY       BIGINT           NULL,
			DEFAULTED        INT              NULL,
			DELETED          INT              NULL
		)
	";

	Database::query($sql);
}

Database::commit();

return true;
