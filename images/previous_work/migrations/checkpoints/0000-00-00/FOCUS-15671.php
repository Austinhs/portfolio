<?php
if (empty($GLOBALS["FocusFinanceConfig"]) || !$GLOBALS["FocusFinanceConfig"]["enabled"]) {
	return false;
}

Database::begin();

if (!Database::tableExists("gl_pr_journal_reallocations_bulk")) {
	Database::query("
		CREATE TABLE gl_pr_journal_reallocations_bulk (
			id bigint,
		    batch_id    bigint,
		    changed int,
			deleted	int,
		    contract_year int,
		    history_wage_id bigint,
		    CONSTRAINT reallocation_bulk_id PRIMARY KEY(id)
		)
	");

	Database::query("
		CREATE INDEX reallocations_bulk_batch_id
		ON gl_pr_journal_reallocations_bulk (batch_id)
	");
}
else
{

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk', 'batch_id')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk', 'batch_id', 'bigint');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk', 'changed')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk', 'changed', 'int');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk', 'contract_year')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk', 'contract_year', 'int');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk', 'history_wage_id')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk', 'history_wage_id', 'bigint');
	}

}


if (!Database::tableExists("gl_pr_journal_reallocations_bulk_action")) {
	Database::query("
		CREATE TABLE gl_pr_journal_reallocations_bulk_action(
			id bigint,
		    batch_id    bigint,
		    contract_year int,
		    allocate_perc numeric,
			actions varchar(999),
		    CONSTRAINT reallocation_bulk_action_id PRIMARY KEY(id)
		)
	");

	Database::query("
		CREATE INDEX reallocations_bulk_action_batch_id
		ON gl_pr_journal_reallocations_bulk_action (batch_id)
	");
}
else
{
	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_action', 'batch_id')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_action', 'batch_id', 'bigint');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_action', 'contract_year')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_action', 'contract_year', 'int');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_action', 'allocate_perc')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_action', 'allocate_perc', 'numeric');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_action', 'actions')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_action', 'actions', 'varchar(999)');
	}
}
if (!Database::tableExists("gl_pr_journal_reallocations_bulk_allocation")) {

	Database::query("
		CREATE TABLE gl_pr_journal_reallocations_bulk_allocation (
			id bigint,
			accounting_strip_id bigint,
			allocation numeric,
			allocate_perc numeric,
		    batch_id    bigint,
		    deleted	int,
		    history_allocation_id bigint,
		    original_allocation numeric,
		    original_allocate_perc numeric,
		    original_accounting_strip_id bigint,
		    parent_id bigint,
		    contract_year int,
		    CONSTRAINT reallocation_bulk_allocation_id PRIMARY KEY(id)
		)
	");

	Database::query("
		CREATE INDEX reallocations_bulk__allo_batch_id
		ON gl_pr_journal_reallocations_bulk_allocation (batch_id)
	");

	Database::query("
		CREATE INDEX reallocations_bulk_allo_parent
		ON gl_pr_journal_reallocations_bulk_allocation (parent_id)
	");

}
else
{
	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_allocation', 'accounting_strip_id')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_allocation', 'accounting_strip_id', 'bigint');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_allocation', 'allocation')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_allocation', 'allocation', 'numeric');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_allocation', 'allocate_perc')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_allocation', 'allocate_perc', 'numeric');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_allocation', 'batch_id')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_allocation', 'batch_id', 'bigint');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_allocation', 'history_allocation_id')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_allocation', 'history_allocation_id', 'bigint');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_allocation', 'original_allocation')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_allocation', 'original_allocation', 'numeric');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_allocation', 'original_allocate_perc')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_allocation', 'original_allocate_perc', 'numeric');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_allocation', 'original_accounting_strip_id')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_allocation', 'original_accounting_strip_id', 'bigint');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_allocation', 'parent_id')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_allocation', 'parent_id', 'bigint');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_allocation', 'contract_year')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_allocation', 'contract_year', 'int');
	}

}

if (!Database::tableExists("gl_pr_journal_reallocations_bulk_batch")) {
	Database::query("
		CREATE TABLE gl_pr_journal_reallocations_bulk_batch (
			id bigint,
			deleted	int,
		    title    varchar(100),
		    processed int,
		    contract_year int,
		    CONSTRAINT reallocations_batch PRIMARY KEY(id)
		)
	");
}
else
{
	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_batch', 'title')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_batch', 'title', 'varchar(100)');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_batch', 'processed')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_batch', 'processed', 'int');
	}

	if(!Database::columnExists('gl_pr_journal_reallocations_bulk_batch', 'contract_year')) {
		Database::createColumn('gl_pr_journal_reallocations_bulk_batch', 'contract_year', 'int');
	}

}

if(!Database::columnExists('gl_pr_journal_reallocations', 'journal_reallocations_bulk_id')) {
	Database::createColumn('gl_pr_journal_reallocations', 'journal_reallocations_bulk_id', 'bigint');
}

Database::commit();
return true;
?>
