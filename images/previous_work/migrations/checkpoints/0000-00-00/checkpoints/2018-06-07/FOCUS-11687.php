<?php
if (!Database::columnExists("edit_rules", "letter_id")) {
	Database::createColumn("edit_rules", "letter_id", "integer");
}

if (!Database::columnExists("edit_rules", "recipient_email_addresses")) {
	Database::createColumn("edit_rules", "recipient_email_addresses", "text");
}

if (!Database::columnExists("edit_rules", "recipient_affected")) {
	Database::createColumn("edit_rules", "recipient_affected", "integer");
}

if (!Database::columnExists("edit_rules", "recipient_affected_parent")) {
	Database::createColumn("edit_rules", "recipient_affected_parent", "integer");
}

if (!Database::columnExists("edit_rules", "recipient_profiles")) {
	Database::createColumn("edit_rules", "recipient_profiles", "text");
}

if (!Database::columnExists("edit_rules", "recipient_users")) {
	Database::createColumn("edit_rules", "recipient_users", "text");
}

if (!Database::columnExists("edit_rules", "workflow_trigger_type")) {
	Database::createColumn("edit_rules", "workflow_trigger_type", "text");
}

if (!Database::columnExists("edit_rule_criteria", "onchange")) {
	Database::createColumn("edit_rule_criteria", "onchange", "integer");
}