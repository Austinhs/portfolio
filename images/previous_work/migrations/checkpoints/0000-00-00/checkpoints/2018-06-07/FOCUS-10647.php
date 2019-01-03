<?php

// Tags: Formbuilder
$currentTable = null;

if (!function_exists('createTable')) {
	function createTable($name) {
		global $currentTable;

		$currentTable = $name;

		if (!Database::tableExists($name)) {
			Database::query(Database::preprocess("
				CREATE TABLE {$name} (
					id BIGINT PRIMARY KEY DEFAULT {{next:gl_maint_seq}}
				)
			"));
		} else if (Database::tableExists('gl_meta_table')) {
			$id = Database::get("SELECT id FROM gl_meta_table WHERE name = '{$name}'");
			if (empty($id)) {
				return;
			}

			$id = $id[0]['ID'];
			if($id) {
				Database::query("DELETE FROM gl_meta_field WHERE meta_table_id = {$id}");
				Database::query("DELETE FROM gl_meta_table WHERE id = {$id}");
			}
		}
	}
}

if (!function_exists('withColumn')) {
	function withColumn($name, $type, $length = '') {
		global $currentTable;

		if (!Database::columnExists($currentTable, $name)) {
			Database::createColumn($currentTable, $name, $type, $length);
		}
	}
}

if (!Database::sequenceExists('gl_maint_seq')) {
	Database::createSequence('gl_maint_seq');
}

createTable("gl_ap_approval_link");
	withColumn("child_node_id", "bigint");
	withColumn("node_id", "bigint");
	withColumn("approval_type", "varchar", 255);

createTable("gl_ap_approval_node");
	withColumn("name", "varchar", 255);
	withColumn("type", "varchar", 255);
	withColumn("x", "bigint");
	withColumn("y", "bigint");
	withColumn("approval_type", "varchar", 255);
	withColumn("label", "varchar", 255);
	withColumn("meta_field_id", "bigint");
	withColumn("value", "varchar", 255);
	withColumn("wildcard_character", "varchar", 255);
	withColumn("operator", "varchar", 255);
	withColumn("field", "varchar", 255);
	withColumn("instance_id", "bigint");

createTable("gl_ap_approval_record");
	withColumn("approved", "varchar", 1);
	withColumn("decision_date", "timestamp");
	withColumn("staff_id", "bigint");
	withColumn("tier", "bigint");
	withColumn("approval_permission_id", "bigint");
	withColumn("approver", "bigint");
	withColumn("source", "varchar", 255);
	withColumn("source_id", "bigint");
	withColumn("sub", "bigint");

createTable("gl_ap_approval_substitute");
	withColumn("staff_id", "bigint");
	withColumn("start_date", "timestamp");
	withColumn("end_date", "timestamp");
	withColumn("substitute", "bigint");
	withColumn("approval_type", "varchar", 255);
	withColumn("instance_id", "bigint");

createTable("gl_ap_approval_permission");
	withColumn("facility", "varchar", 255);
	withColumn("function", "varchar", 255);
	withColumn("fund", "varchar", 255);
	withColumn("grant_element", "varchar", 255);
	withColumn("node_id", "bigint");
	withColumn("object", "varchar", 255);
	withColumn("program", "varchar", 255);
	withColumn("parent_object_id", "varchar", 255);
	withColumn("staff_id", "bigint");

createTable("gl_comments");
	withColumn("comment", "varchar", 255);
	withColumn("posted_from", "varchar", 255);
	withColumn("source", "varchar", 255);
	withColumn("source_id", "varchar", 255);
	withColumn("staff_id", "bigint");
	withColumn("time", "bigint");

createTable("gl_element_category");
	withColumn("sort", "bigint");
	withColumn("title", "varchar", 255);
	withColumn("type", "varchar", 1);
	withColumn("version", "bigint");
	withColumn("budgeted", "bigint");
	withColumn("deleted", "bigint");
	withColumn("length", "bigint");
	withColumn("meta_field_id", "bigint");
	withColumn("name", "varchar", 255);
	withColumn("expense", "bigint");
	withColumn("revenue", "bigint");
	withColumn("sort_substitute_strips", "bigint");
	withColumn("internal", "bigint");
	withColumn("updated_at", "bigint");

createTable("gl_user_tasks");
	withColumn("active", "varchar", 1);
	withColumn("created_time", "bigint");
	withColumn("link", "varchar", 255);
	withColumn("sort", "bigint");
	withColumn("source", "varchar", 255);
	withColumn("source_id", "bigint");
	withColumn("staff_id", "bigint");
	withColumn("text", "varchar", 255);

createTable("gl_permission");
	withColumn("approval_type", "varchar", 255);
	withColumn("staff_id", "bigint");
	withColumn("approval_node_id", "bigint");
	withColumn("node_id", "bigint");
	withColumn("e_facility", "varchar", 255);
	withColumn("e_function", "varchar", 255);
	withColumn("e_fund", "varchar", 255);
	withColumn("grant_element", "varchar", 255);
	withColumn("e_object", "varchar", 255);
	withColumn("e_program", "varchar", 255);
	withColumn("e_project", "varchar", 255);
	withColumn("type", "varchar", 255);
	withColumn("updated_at", "timestamp");
	withColumn("field", "varchar", 255);
	withColumn("readonly", "bigint");
	withColumn("hr", "bigint");
	withColumn("instance_id", "bigint");
	withColumn("sub", "bigint");
	withColumn("ap", "bigint");

createTable("gl_permission_value");
	withColumn("permission_id", "bigint");
	withColumn("meta_field_id", "bigint");
	withColumn("operator", "varchar", 255);
	withColumn("value", "varchar", 255);
	withColumn("wildcard_character", "varchar", 255);
	withColumn("field", "varchar", 255);
	withColumn("updated_at", "timestamp");

createTable("gl_requests");
	withColumn("deleted", "bigint");
	withColumn("title", "varchar", 255);
	withColumn("created_by", "bigint");
	withColumn("request_date", "timestamp");
	withColumn("status", "varchar", 255);
	withColumn("parent_class", "varchar", 255);
	withColumn("parent_id", "bigint");
	withColumn("instance_id", "bigint");

createTable("gl_setting");
	withColumn("deleted", "bigint");
	withColumn("key", "varchar", 255);
	withColumn("value", "text");
	withColumn("json", "bigint");
