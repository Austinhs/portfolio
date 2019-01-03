<?php

$gradebook_template_tables = ['gradebook_templates', 'gradebook_templates_assignments', 'gradebook_templates_categories', 'gradebook_templates_join_courses', 'gradebook_templates_join_excluded_courses'];

foreach($gradebook_template_tables as $template_table) {
	if (!Database::columnExists($template_table, 'rollover_id')) {
			Database::createColumn($template_table, 'rollover_id', 'INT');
	}
}
