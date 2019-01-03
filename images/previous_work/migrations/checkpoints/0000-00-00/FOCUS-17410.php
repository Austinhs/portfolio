<?php

if (!Database::tableExists('imm_ruleset_groups_rulesets') || !Database::tableExists('imm_rulesets_rules')){
	return false;
}

if (!Database::$type === 'postgres'){
	return false;
}

Database::query("
	UPDATE program_config 
		set value = 'Ruleset' 
	where 
		program = 'system' 
		and title = 'IMM_ERROR_HANDLING' 
		and syear = (select cast(value as int) from program_config where program = 'system' and title = 'DEFAULT_S_YEAR' and syear is null and school_id is null) 
		and school_id is null
		and value = 'Itemized';
");