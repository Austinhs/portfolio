<?php
if (Database::tableExists('ps_fa_pay_periods'))
{
	if (!Database::columnExists('ps_fa_pay_periods', 'sap_status'))
	{
		Database::createColumn('ps_fa_pay_periods', 'sap_status', 'VARCHAR', 1);
	}
}
