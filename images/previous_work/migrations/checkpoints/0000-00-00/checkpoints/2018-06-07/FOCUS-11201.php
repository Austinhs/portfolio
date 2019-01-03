<?php


$addDatabaseColumn = function($tableName, $columnName, $type)
{
	if (!Database::columnExists($tableName, $columnName)) {
		Database::createColumn($tableName, $columnName, $type);
	}
};

Database::begin();

$timestampName = (Database::$type == "mssql") ? "DATETIME2" : "TIMESTAMP";
$identity      = (Database::$type == "mssql") ? "IDENTITY(1,1) PRIMARY KEY" : "PRIMARY KEY";
$textName      = (Database::$type === "mssql") ? "VARCHAR(MAX)" : "TEXT";

if (!Database::sequenceExists('lesson_plan_seq')) {
	Database::createSequence('lesson_plan_seq');
}

if (!Database::tableExists('lesson_plan')) {
	Database::query("
		CREATE TABLE lesson_plan (
			id NUMERIC {$identity},
			title VARCHAR(255),
			staff_id NUMERIC NOT NULL,
			syear NUMERIC,
			last_updated {$timestampName},
			tags {$textName}
		);
	");
}

if (!Database::sequenceExists('lesson_layout_seq')) {
	Database::createSequence('lesson_layout_seq');
}

if (!Database::tableExists('lesson_layout')) {
	Database::query("
		CREATE TABLE lesson_layout
		(
			id NUMERIC {$identity},
			lesson_plan_id NUMERIC,
			course_id BIGINT,
			course_period_id BIGINT,
			staff_id BIGINT,
			syear NUMERIC,
			data {$textName},
			sequence NUMERIC,
			title VARCHAR(255),
			visibility CHAR(1),
			standards VARCHAR(512),
			notes VARCHAR(1024),
			reviewed CHAR(1),
			format VARCHAR(16),
			start_date {$timestampName},
			end_date {$timestampName},
			start_date_enabled CHAR(1),
			end_date_enabled CHAR(1),
			last_updated DATE,
			component_data {$textName},
			marking_period_id BIGINT,
			saved CHAR(1)
		);
	");
}

if (!Database::sequenceExists('lesson_course_seq')) {
	Database::createSequence('lesson_course_seq');
}

if (!Database::tableExists('lesson_course')) {
	Database::query("
		CREATE TABLE lesson_course
		(
			id BIGINT {$identity},
			lesson_plan_id NUMERIC NOT NULL,
			course_num VARCHAR(16),
			course_period_id NUMERIC
		);
	");
}

if (!Database::sequenceExists('lesson_files_seq')) {
	Database::createSequence('lesson_files_seq');
}

if (!Database::tableExists('lesson_files')) {
	Database::query("
		CREATE TABLE lesson_files
		(
			id NUMERIC {$identity},
			lesson NUMERIC,
			course_period_id NUMERIC,
			path VARCHAR(1024),
			original_name VARCHAR(1024),
			upload_time DATE,
			size NUMERIC,
			staff_id BIGINT,
			hash VARCHAR(64)
		);
	");
}

if (!Database::sequenceExists('forum_seq')) {
	Database::createSequence('forum_seq');
}

if (!Database::tableExists('forum')) {
	Database::query("
		CREATE TABLE forum
		(
			id NUMERIC {$identity},
			title VARCHAR(500),
			creator NUMERIC NOT NULL,
			intro {$textName},
			time_created BIGINT,
			settings {$textName},
			sections VARCHAR(255),
			assignments VARCHAR(255),
			lesson_id NUMERIC,
			course_id NUMERIC,
			assigned_date {$timestampName},
			due_date {$timestampName}
		);
	");
}

if (!Database::sequenceExists('forum_thread_seq')) {
	Database::createSequence('forum_thread_seq');
}

if (!Database::tableExists('forum_thread')) {
	Database::query("
		CREATE TABLE forum_thread
		(
			id NUMERIC {$identity},
			forum_id NUMERIC NOT NULL,
			author NUMERIC NOT NULL,
			title VARCHAR(500),
			\"open\" CHAR(1),
			hidden CHAR(1),
			views NUMERIC,
			time_created BIGINT,
			last_edit_time BIGINT,
			course_period_id BIGINT
		);
	");
}

if (!Database::sequenceExists('forum_post_seq')) {
	Database::createSequence('forum_post_seq');
}

if (!Database::tableExists('forum_post')) {
	Database::query("
		CREATE TABLE forum_post
		(
			id NUMERIC {$identity},
			author NUMERIC NOT NULL,
			time_posted BIGINT,
			reply_to NUMERIC,
			last_edit_time BIGINT,
			approved CHAR(1),
			thread_id NUMERIC NOT NULL,
			content {$textName},
			sequence NUMERIC,
			grade VARCHAR(30),
			course_period_id NUMERIC,
			approval_time BIGINT
		);
	");
}

if (!Database::sequenceExists('forum_user_seq')) {
	Database::createSequence('forum_user_seq');
}

if (!Database::tableExists('forum_user')) {
	Database::query("
		CREATE TABLE forum_user
		(
			id NUMERIC {$identity},
			focus_id NUMERIC NOT NULL,
			focus_profile VARCHAR(16) NOT NULL,
			post_count NUMERIC
		);
	");
}

if (!Database::sequenceExists('forum_thread_view_seq')) {
	Database::createSequence('forum_thread_view_seq');
}

if (!Database::tableExists('forum_thread_view')) {
	Database::query("
		CREATE TABLE forum_thread_view
		(
			forum_id NUMERIC,
			thread_id NUMERIC NOT NULL,
			user_id NUMERIC NOT NULL,
			last_viewed BIGINT
		);
	");
}

if (!Database::sequenceExists('lesson_plan_component_seq')) {
	Database::createSequence('lesson_plan_component_seq');
}

if (!Database::tableExists('lesson_plan_component')) {
	Database::query("
		CREATE TABLE lesson_plan_component
		(
			id NUMERIC {$identity},
			\"level\" CHAR(1) NOT NULL,
			type VARCHAR(32) NOT NULL,
			description {$textName},
			title VARCHAR(256),
			default_value {$textName},
			sort_order NUMERIC,
			disabled CHAR(1),
			restriction CHAR(1),
			student_view CHAR(1),
			alt_titles {$textName},
			\"public\" CHAR(1)
		);
	");

	Database::query("INSERT INTO lesson_plan_component (ID, \"LEVEL\", TYPE, DESCRIPTION, TITLE, DEFAULT_VALUE, SORT_ORDER, DISABLED, STUDENT_VIEW, RESTRICTION, ALT_TITLES) values(nextval('lesson_plan_component_seq'), 'm', 'plain', 'Students will be able to/Understand:', 'Objectives', '', '1', '', 'Y', 'n', '');");
	Database::query("INSERT INTO lesson_plan_component (ID, \"LEVEL\", TYPE, DESCRIPTION, TITLE, DEFAULT_VALUE, SORT_ORDER, DISABLED, STUDENT_VIEW, RESTRICTION, ALT_TITLES) values(nextval('lesson_plan_component_seq'), 'm', 'plain', '', 'Essential Content', '', '2', '', 'Y', 'n', '');");
	Database::query("INSERT INTO lesson_plan_component (ID, \"LEVEL\", TYPE, DESCRIPTION, TITLE, DEFAULT_VALUE, SORT_ORDER, DISABLED, STUDENT_VIEW, RESTRICTION, ALT_TITLES) values(nextval('lesson_plan_component_seq'), 'm', 'plain', '', 'Evidence of Learning', '', '3', '', 'Y', 'n', '');");
	Database::query("INSERT INTO lesson_plan_component (ID, \"LEVEL\", TYPE, DESCRIPTION, TITLE, DEFAULT_VALUE, SORT_ORDER, DISABLED, STUDENT_VIEW, RESTRICTION, ALT_TITLES) values(nextval('lesson_plan_component_seq'), 'l', 'plain', '', 'Objective', '', '1', '', 'Y', 'n', '');");
	Database::query("INSERT INTO lesson_plan_component (ID, \"LEVEL\", TYPE, DESCRIPTION, TITLE, DEFAULT_VALUE, SORT_ORDER, DISABLED, STUDENT_VIEW, RESTRICTION, ALT_TITLES) values(nextval('lesson_plan_component_seq'), 'l', 'plain', '', 'Essential Question', '', '2', '', 'Y', 'n', '');");
	Database::query("INSERT INTO lesson_plan_component (ID, \"LEVEL\", TYPE, DESCRIPTION, TITLE, DEFAULT_VALUE, SORT_ORDER, DISABLED, STUDENT_VIEW, RESTRICTION, ALT_TITLES) values(nextval('lesson_plan_component_seq'), 'l', 'rich', '', 'Resources/Activities/Lessons', '', '3', '', 'Y', 'n', '');");
	Database::query("INSERT INTO lesson_plan_component (ID, \"LEVEL\", TYPE, DESCRIPTION, TITLE, DEFAULT_VALUE, SORT_ORDER, DISABLED, STUDENT_VIEW, RESTRICTION, ALT_TITLES) values(nextval('lesson_plan_component_seq'), 'l', 'plain', '', 'Higher Order Questions', '', '4', '', 'Y', 'n', '');");
	Database::query("INSERT INTO lesson_plan_component (ID, \"LEVEL\", TYPE, DESCRIPTION, TITLE, DEFAULT_VALUE, SORT_ORDER, DISABLED, STUDENT_VIEW, RESTRICTION, ALT_TITLES) values(nextval('lesson_plan_component_seq'), 'l', 'plain', '', 'Vocabulary', '', '5', '', 'Y', 'n', '');");
	Database::query("INSERT INTO lesson_plan_component (ID, \"LEVEL\", TYPE, DESCRIPTION, TITLE, DEFAULT_VALUE, SORT_ORDER, DISABLED, STUDENT_VIEW, RESTRICTION, ALT_TITLES) values(nextval('lesson_plan_component_seq'), 'l', 'rich', '', 'ELL Strategies', '', '6', '', 'Y', 'n', '');");

}

if (!Database::sequenceExists('instruction_group_seq')) {
	Database::createSequence('instruction_group_seq');
}

if (!Database::tableExists('instruction_group')) {
	Database::query("
		CREATE TABLE instruction_group
		(
			id BIGINT {$identity},
			student_id BIGINT NOT NULL,
			course_period_id BIGINT NOT NULL,
			date DATE NOT NULL,
			learning_group VARCHAR(255),
			rotation VARCHAR(255),
			description VARCHAR(255),
			start_time VARCHAR(20),
			end_time VARCHAR(20),
			standard BIGINT
		);
	");
}

if (!Database::sequenceExists('lesson_plan_sharing_seq')) {
	Database::createSequence('lesson_plan_sharing_seq');
}

if (!Database::tableExists('lesson_plan_sharing')) {
	Database::query("
		CREATE TABLE lesson_plan_sharing
		(
			id BIGINT {$identity},
			lesson_plan_id BIGINT,
			source_user BIGINT,
			shared_to BIGINT,
			shared_type VARCHAR(16),
			access BIGINT,
			date_CREATEd date
		);
	");
}

if (!Database::sequenceExists('lesson_plan_sharing_group_seq')) {
	Database::createSequence('lesson_plan_sharing_group_seq');
}

if (!Database::tableExists('lesson_plan_sharing_group')) {
	Database::query("
		CREATE TABLE lesson_plan_sharing_group
		(
			id BIGINT {$identity},
			name VARCHAR(64),
			school_id BIGINT,
			code VARCHAR(16)
		);
	");
}

if (!Database::sequenceExists('lesson_plan_sharing_group_member_seq')) {
	Database::createSequence('lesson_plan_sharing_group_member_seq');
}

if (!Database::tableExists('lesson_plan_sharing_group_member')) {
	Database::query("
		CREATE TABLE lesson_plan_sharing_group_member
		(
			id BIGINT {$identity},
			group_id BIGINT,
			user_id BIGINT
		);
	");
}

if (!Database::sequenceExists('teacher_notification_seq')) {
	Database::createSequence('teacher_notification_seq');
}

if (!Database::tableExists('teacher_notification')) {
	Database::query("
		CREATE TABLE teacher_notification
		(
			id BIGINT {$identity},
			source_user BIGINT,
			teacher BIGINT,
			message {$textName},
			time_posted {$timestampName},
			time_read {$timestampName},
			program VARCHAR(255),
			title VARCHAR(255)
		);
	");
}

$addDatabaseColumn("lesson_layout", "marking_period_id", "bigint");
$addDatabaseColumn("lesson_plan", "syear", "numeric");
$addDatabaseColumn("lesson_files", "hash", "varchar(64)");

if (!Database::columnExists("lesson_layout", "saved")) {
	Database::createColumn("lesson_layout", "saved", "char(1)");
	Database::query("UPDATE lesson_layout SET saved = 'Y'");
}

$addDatabaseColumn("lesson_files", "staff_id", "bigint");
$addDatabaseColumn("forum", "assigned_date", $timestampName);

if (!Database::indexExists("lesson_layout", "lesson_layout_ind1")) {
	Database::query("CREATE INDEX lesson_layout_ind1 ON lesson_layout(lesson_plan_id);");
}

if (!Database::indexExists("lesson_layout", "lesson_layout_ind2")) {
	Database::query("CREATE INDEX lesson_layout_ind2 ON lesson_layout(staff_id);");
}

if (!Database::indexExists("lesson_layout", "lesson_layout_ind3")) {
	Database::query("CREATE INDEX lesson_layout_ind3 ON lesson_layout(course_period_id);");
}

if (!Database::indexExists("lesson_course", "lesson_course_ind1")) {
	Database::query("CREATE INDEX lesson_course_ind1 ON lesson_course(lesson_plan_id);");
}

if (!Database::indexExists("forum", "forum_ind1")) {
	Database::query("CREATE INDEX forum_ind1 ON forum(lesson_id)");
}

if (!Database::indexExists("forum", "forum_ind2")) {
	Database::query("CREATE INDEX forum_ind2 ON forum(course_id)");
}

if (!Database::indexExists("forum_thread", "forum_thread_ind1")) {
	Database::query("CREATE INDEX forum_thread_ind1 ON forum_thread(forum_id)");
}

if (!Database::indexExists("forum_thread", "forum_thread_ind2")) {
	Database::query("CREATE INDEX forum_thread_ind2 ON forum_thread(course_period_id)");
}

if (!Database::indexExists("forum_thread", "forum_thread_ind3")) {
	Database::query("CREATE INDEX forum_thread_ind3 ON forum_thread(author)");
}

if (!Database::indexExists("forum_post", "forum_post_ind1")) {
	Database::query("CREATE INDEX forum_post_ind1 ON forum_post(thread_id)");
}

if (!Database::indexExists("forum_post", "forum_post_ind2")) {
	Database::query("CREATE INDEX forum_post_ind2 ON forum_post(course_period_id)");
}

if (!Database::indexExists("forum_post", "forum_post_ind3")) {
	Database::query("CREATE INDEX forum_post_ind3 ON forum_post(author)");
}

if (!Database::indexExists("forum_user", "forum_user_ind1")) {
	Database::query("CREATE INDEX forum_user_ind1 ON forum_user(focus_id)");
}

if (!Database::indexExists("forum_thread_view", "forum_thread_view_ind1")) {
	Database::query("CREATE INDEX forum_thread_view_ind1 ON forum_thread_view(forum_id)");
}

if (!Database::indexExists("instruction_group", "instruction_group_ind1")) {
	Database::query("CREATE INDEX instruction_group_ind1 ON instruction_group(student_id)");
}

if (!Database::indexExists("instruction_group", "instruction_group_ind2")) {
	Database::query("CREATE INDEX instruction_group_ind2 ON instruction_group(course_period_id)");
}

if (!Database::indexExists("instruction_group", "instruction_group_ind3")) {
	if(Database::columnExists('instruction_group', 'date')) {
		Database::query("CREATE INDEX instruction_group_ind3 ON instruction_group(date)");
	}
	else if(Database::columnExists('instruction_group', 'group_date')) {
		Database::query("CREATE INDEX instruction_group_ind3 ON instruction_group(group_date)");
	}
}

$addDatabaseColumn("lesson_plan", "components", "varchar(500)");
$addDatabaseColumn("lesson_plan", "template", "char(1)");
$addDatabaseColumn("lesson_layout", "locked","char(1)");
$addDatabaseColumn("lesson_layout", "template", "char(1)");
$addDatabaseColumn("lesson_plan_component", "options", "text");

if (!Database::sequenceExists('lesson_plan_component_template_seq')) {
	Database::createSequence('lesson_plan_component_template_seq');
}

if (!Database::tableExists('lesson_plan_component_template')) {
	Database::query("
		CREATE TABLE lesson_plan_component_template
		(
			id BIGINT {$identity},
			title VARCHAR(128),
			components VARCHAR(128)
		);
  	");
}

$addDatabaseColumn("lesson_plan", "permissions", "text");
$addDatabaseColumn("lesson_plan", "import_id", "bigint");
$addDatabaseColumn("lesson_layout", "plan_import_id", "bigint");
$addDatabaseColumn("lesson_layout", "unit_import_id", "bigint");
$addDatabaseColumn("lesson_layout", "duration", "bigint");
$addDatabaseColumn("teacher_notification", "notification_type", "varchar(255)");
$addDatabaseColumn("teacher_notification", "target_user", "numeric");

if (Database::columnExists("teacher_notification", "teacher")) {
	Database::query("ALTER TABLE teacher_notification DROP COLUMN teacher");
}

if (!Database::sequenceExists('lesson_plan_category_seq')) {
	Database::createSequence('lesson_plan_category_seq');
}

if (!Database::tableExists('lesson_plan_category')) {
	Database::query("
		CREATE TABLE lesson_plan_category (
			id NUMERIC {$identity},
			name VARCHAR(200),
			staff_id NUMERIC NOT NULL
		);
  	");
}

$addDatabaseColumn("lesson_plan", "category_id", "numeric");

if (!Database::indexExists("lesson_plan_category", "lesson_plan_category_staff_id_ind1")) {
	Database::query("CREATE INDEX lesson_plan_category_staff_id_ind1 ON lesson_plan_category(staff_id)");
}
if (!Database::indexExists("lesson_plan_category", "lesson_plan_category_id_ind1")) {
	Database::query("CREATE INDEX lesson_plan_category_id_ind1 ON lesson_plan_category(id)");
}
if (!Database::indexExists("lesson_plan", "lesson_plan_category_id_ind2")) {
	Database::query("CREATE INDEX lesson_plan_category_id_ind2 ON lesson_plan(category_id)");
}

if (Database::columnExists("instruction_group", "date")) {
	Database::renameColumn("date", "group_date", "instruction_group");
}

$addDatabaseColumn("lesson_plan", "web_page_id", "numeric");

Database::commit();
