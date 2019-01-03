<?php

if (!Database::columnExists("custom_fields", "suggestion_query")) {
	Database::createColumn("custom_fields", "suggestion_query", "TEXT");
}

if (!Database::columnExists("custom_field_log_columns", "suggestion_query")) {
	Database::createColumn("custom_field_log_columns", "suggestion_query", "TEXT");
}
