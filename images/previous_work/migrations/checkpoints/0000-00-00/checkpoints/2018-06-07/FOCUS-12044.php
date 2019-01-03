<?php


Database::query("
	UPDATE
		ps_fees
	SET
		parent_id = pfg.id
	FROM
		ps_fee_templates pft
		JOIN ps_fee_groups pfg ON pft.id = pfg.template_id
	WHERE
		ps_fees.parent_id = pft.group_id AND
		ps_fees.syear = pft.syear AND
		ps_fees.syear = '2017' AND
		pfg.id != ps_fees.parent_id
");

Database::query("
	UPDATE
		ps_fee_templates
	set
		group_id = pfg.id
	FROM
		ps_fee_groups pfg
	WHERE
		ps_fee_templates.id = pfg.template_id AND
		ps_fee_templates.syear = '2017' AND
		ps_fee_templates.group_id != pfg.id
");
