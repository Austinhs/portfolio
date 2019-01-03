<?php
	if (!Database::columnExists('importer_test_history_parser_templates', 'test_short_name')) {
		Database::createColumn('importer_test_history_parser_templates', 'test_short_name', 'varchar');
	}