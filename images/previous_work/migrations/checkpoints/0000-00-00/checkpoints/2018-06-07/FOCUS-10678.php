<?php

if(empty($GLOBALS['FocusFinanceConfig']) || !$GLOBALS['FocusFinanceConfig']['enabled']) {
	return false;
}

if(!Database::tableExists('ps_billing_invoice_history')) {
	Database::query("
		CREATE TABLE ps_billing_invoice_history (
			id BIGINT NOT NULL,
			deleted BIGINT NULL,
			invoice_id BIGINT NULL,
			schedule_id BIGINT NULL,
			bill_by VARCHAR,
			course_id BIGINT NULL,
			course_period_id BIGINT NULL
		)
	");
}

if(Database::columnExists(POSInvoiceAllocation::$table, 'course_periods')) {
	$allocations = POSInvoiceAllocation::getAllAndLoad('course_periods is not null');

	foreach($allocations as $allocation_id => $allocation) {
		$course_periods = $allocation->getCoursePeriods();
		$course_periods = json_decode($course_periods, true);
		$invoice_id     = $allocation->getInvoiceId();
		$invoice        = new POSInvoice($invoice_id);
		$customer       = new Customer($invoice->getCustomerId());
		$student_id     = $customer->getParentId();

		foreach($course_periods as $course_id => $sections) {
			foreach($sections as $course_period_id) {
				$schedule_where = [
					"course_period_id = '{$course_period_id}'",
					"student_id = '{$student_id}'"
				];

				$schedules = Schedule::getAllAndLoad($schedule_where);

				if(!empty($schedules)) {
					PsBillingInvoiceHistory::generate($invoice_id, array_keys($schedules));
				}
				else {
					(new PsBillingInvoiceHistory)
						->setInvoiceId($invoice_id)
						->setCoursePeriodId($course_period_id)
						->setCourseId($course_id)
						->persist();
				}
			}
		}
	}
}
