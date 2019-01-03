<?php
if (empty($GLOBALS['FocusFinanceConfig']['enabled'])) {
	return false;
}
	//svn merge svn://focus-sis.com/focus/branches/8.0/dev/FOCUS-16572 -r252204:HEAD

	Database::begin();

	if (!Database::columnExists("gl_pr_history_run_wage_allocations", "fyear")) {
		Database::createColumn("gl_pr_history_run_wage_allocations", "fyear", "bigint");

		Database::query("
			UPDATE gl_pr_history_run_wage_allocations
			SET fyear = (
				SELECT fyear
				FROM gl_pr_history_run_wages rd
				WHERE rd.id = gl_pr_history_run_wage_allocations.wage_history_id
				)
		");

		Database::query("
				CREATE INDEX wage_allo_fyear ON gl_pr_history_run_wage_allocations (fyear);
		");
	}

	if (!Database::columnExists("gl_pr_history_run_deduction_allocations", "fyear")) {
		Database::createColumn("gl_pr_history_run_deduction_allocations", "fyear", "bigint");

		Database::query("
			UPDATE gl_pr_history_run_deduction_allocations
			SET fyear = (
				SELECT rd.fyear
				FROM gl_pr_history_run_deductions rd
				WHERE rd.id = gl_pr_history_run_deduction_allocations.deduction_history_id
			)
		");

		Database::query("
				CREATE INDEX ded_allo_fyear ON gl_pr_history_run_deduction_allocations (fyear);
		");
	}

	if(Database::$type === 'postgres')
	{
		if (Database::columnExists("gl_pr_history_run_deduction_allocations", "e_facility")) {
			Database::dropColumn("gl_pr_history_run_deduction_allocations", "e_facility");
		}

		if (Database::columnExists("gl_pr_history_run_deduction_allocations", "e_function")) {
			Database::dropColumn("gl_pr_history_run_deduction_allocations", "e_function");
		}

		if (Database::columnExists("gl_pr_history_run_deduction_allocations", "e_fund")) {
			Database::dropColumn("gl_pr_history_run_deduction_allocations", "e_fund");
		}

		if (Database::columnExists("gl_pr_history_run_deduction_allocations", "e_object")) {
			Database::dropColumn("gl_pr_history_run_deduction_allocations", "e_object");
		}

		if (Database::columnExists("gl_pr_history_run_deduction_allocations", "e_program")) {
			Database::dropColumn("gl_pr_history_run_deduction_allocations", "e_program");
		}

		if (Database::columnExists("gl_pr_history_run_deduction_allocations", "e_project")) {
			Database::dropColumn("gl_pr_history_run_deduction_allocations", "e_project");
		}


		if (Database::columnExists("gl_pr_history_run_deduction_allocations", "facility")) {
			Database::dropColumn("gl_pr_history_run_deduction_allocations", "facility");
		}

		if (Database::columnExists("gl_pr_history_run_deduction_allocations", "function")) {
			Database::dropColumn("gl_pr_history_run_deduction_allocations", "function");
		}

		if (Database::columnExists("gl_pr_history_run_deduction_allocations", "fund")) {
			Database::dropColumn("gl_pr_history_run_deduction_allocations", "fund");
		}

		if (Database::columnExists("gl_pr_history_run_deduction_allocations", "object")) {
			Database::dropColumn("gl_pr_history_run_deduction_allocations", "object");
		}

		if (Database::columnExists("gl_pr_history_run_deduction_allocations", "program")) {
			Database::dropColumn("gl_pr_history_run_deduction_allocations", "program");
		}

		if (Database::columnExists("gl_pr_history_run_deduction_allocations", "project")) {
			Database::dropColumn("gl_pr_history_run_deduction_allocations", "project");
		}









		if (Database::columnExists("gl_pr_history_run_wage_allocations", "e_facility")) {
			Database::dropColumn("gl_pr_history_run_wage_allocations", "e_facility");
		}

		if (Database::columnExists("gl_pr_history_run_wage_allocations", "e_function")) {
			Database::dropColumn("gl_pr_history_run_wage_allocations", "e_function");
		}

		if (Database::columnExists("gl_pr_history_run_wage_allocations", "e_fund")) {
			Database::dropColumn("gl_pr_history_run_wage_allocations", "e_fund");
		}

		if (Database::columnExists("gl_pr_history_run_wage_allocations", "e_object")) {
			Database::dropColumn("gl_pr_history_run_wage_allocations", "e_object");
		}

		if (Database::columnExists("gl_pr_history_run_wage_allocations", "e_program")) {
			Database::dropColumn("gl_pr_history_run_wage_allocations", "e_program");
		}

		if (Database::columnExists("gl_pr_history_run_wage_allocations", "e_project")) {
			Database::dropColumn("gl_pr_history_run_wage_allocations", "e_project");
		}





		if (Database::columnExists("gl_pr_history_run_wage_allocations", "facility")) {
			Database::dropColumn("gl_pr_history_run_wage_allocations", "facility");
		}

		if (Database::columnExists("gl_pr_history_run_wage_allocations", "function")) {
			Database::dropColumn("gl_pr_history_run_wage_allocations", "function");
		}

		if (Database::columnExists("gl_pr_history_run_wage_allocations", "fund")) {
			Database::dropColumn("gl_pr_history_run_wage_allocations", "fund");
		}

		if (Database::columnExists("gl_pr_history_run_wage_allocations", "object")) {
			Database::dropColumn("gl_pr_history_run_wage_allocations", "object");
		}

		if (Database::columnExists("gl_pr_history_run_wage_allocations", "program")) {
			Database::dropColumn("gl_pr_history_run_wage_allocations", "program");
		}

		if (Database::columnExists("gl_pr_history_run_wage_allocations", "project")) {
			Database::dropColumn("gl_pr_history_run_wage_allocations", "project");
		}

	}




	Database::commit();
