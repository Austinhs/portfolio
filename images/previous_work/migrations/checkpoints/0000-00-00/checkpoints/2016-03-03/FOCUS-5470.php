<?php

Migrations::depend('FOCUS-6359');
Migrations::depend('FOCUS-5468');

// Begin 8.0.1

if(!Database::columnExists('custom_field_select_options', 'min_syear')) {
	Database::createColumn('custom_field_select_options', 'min_syear', 'bigint');
}

if(!Database::columnExists('custom_field_select_options', 'max_syear')) {
	Database::createColumn('custom_field_select_options', 'max_syear', 'bigint');
}

if(!Database::columnExists('address_to_district', 'min_apt')) {
	Database::createColumn('address_to_district', 'min_apt', 'varchar');
}

if(!Database::columnExists('address_to_district', 'max_apt')) {
	Database::createColumn('address_to_district', 'max_apt', 'varchar');
}

// End 8.0.1

// Begin 8.0.2

$var1_ref = Database::$type === 'postgres' ? '$1' : '@id';
$var2_ref = Database::$type === 'postgres' ? '$2' : '@match';

// Create functions to make selecting select options easier
if(Database::$type === 'postgres') {
	$functions_sql = [
		"
		DROP FUNCTION IF EXISTS FieldOptionLabel (VARCHAR)
		",
		"
		CREATE OR REPLACE FUNCTION FieldOptionLabel (VARCHAR) RETURNS VARCHAR AS $$
			SELECT label FROM custom_field_select_options WHERE ({{is_int:{$var1_ref}}}) AND id = CAST({$var1_ref} AS BIGINT)
		$$ LANGUAGE SQL
		",
		"
		DROP FUNCTION IF EXISTS FieldOptionCode (VARCHAR)
		",
		"
		CREATE OR REPLACE FUNCTION FieldOptionCode (VARCHAR) RETURNS VARCHAR AS $$
			SELECT code FROM custom_field_select_options WHERE ({{is_int:{$var1_ref}}}) AND id = CAST({$var1_ref} AS BIGINT)
		$$ LANGUAGE SQL
		",
		"
		DROP FUNCTION IF EXISTS FieldOptionLabel (BIGINT)
		",
		"
		CREATE OR REPLACE FUNCTION FieldOptionLabel (BIGINT) RETURNS VARCHAR AS $$
			SELECT label FROM custom_field_select_options WHERE id = {$var1_ref}
		$$ LANGUAGE SQL
		",
		"
		DROP FUNCTION IF EXISTS FieldOptionCode (BIGINT)
		",
		"
		CREATE OR REPLACE FUNCTION FieldOptionCode (BIGINT) RETURNS VARCHAR AS $$
			SELECT code FROM custom_field_select_options WHERE id = {$var1_ref}
		$$ LANGUAGE SQL
		",
		"
		DROP FUNCTION IF EXISTS FieldOptionWhereLabel (VARCHAR, VARCHAR)
		",
		"
		CREATE OR REPLACE FUNCTION FieldOptionWhereLabel (VARCHAR, VARCHAR) RETURNS INT AS $$
			SELECT (
				CASE WHEN
					EXISTS (SELECT 1 FROM custom_field_select_options WHERE label = {$var2_ref} AND ({{is_int:{$var1_ref}}}) AND id = CAST({$var1_ref} AS BIGINT))
				THEN 1
				ELSE 0
				END
			)
		$$ LANGUAGE SQL
		",
		"
		DROP FUNCTION IF EXISTS FieldOptionWhereCode (VARCHAR, VARCHAR)
		",
		"
		CREATE OR REPLACE FUNCTION FieldOptionWhereCode (VARCHAR, VARCHAR) RETURNS INT AS $$
			SELECT (
				CASE WHEN
					EXISTS (SELECT 1 FROM custom_field_select_options WHERE code = {$var2_ref} AND ({{is_int:{$var1_ref}}}) AND id = CAST({$var1_ref} AS BIGINT))
				THEN 1
				ELSE 0
				END
			)
		$$ LANGUAGE SQL
		",
		"
		DROP FUNCTION IF EXISTS FieldOptionWhereLabel (BIGINT, VARCHAR)
		",
		"
		CREATE OR REPLACE FUNCTION FieldOptionWhereLabel (BIGINT, VARCHAR) RETURNS INT AS $$
			SELECT (
				CASE WHEN
					EXISTS(SELECT 1 FROM custom_field_select_options WHERE label = {$var2_ref} AND id = {$var1_ref})
				THEN 1
				ELSE 0
				END
			)
		$$ LANGUAGE SQL
		",
		"
		DROP FUNCTION IF EXISTS FieldOptionWhereCode (BIGINT, VARCHAR)
		",
		"
		CREATE OR REPLACE FUNCTION FieldOptionWhereCode (BIGINT, VARCHAR) RETURNS INT AS $$
			SELECT (
				CASE WHEN
					EXISTS(SELECT 1 FROM custom_field_select_options WHERE code = {$var2_ref} AND id = {$var1_ref})
				THEN 1
				ELSE 0
				END
			)
		$$ LANGUAGE SQL
		"
	];
}
else if(Database::$type === 'mssql') {
	$functions_sql = [
		"
		IF OBJECT_ID('FieldOptionLabel', 'FN') IS NOT NULL DROP FUNCTION dbo.FieldOptionLabel
		",
		"
		CREATE FUNCTION FieldOptionLabel (@id SQL_VARIANT) RETURNS VARCHAR(MAX) AS
			BEGIN
				DECLARE @label AS VARCHAR(MAX);
				SELECT @label = label FROM custom_field_select_options WHERE ({{is_int:{$var1_ref}}}) AND id = CAST({$var1_ref} AS BIGINT);
				RETURN @label;
			END
		",
		"
		IF OBJECT_ID('FieldOptionCode', 'FN') IS NOT NULL DROP FUNCTION dbo.FieldOptionCode
		",
		"
		CREATE FUNCTION FieldOptionCode (@id SQL_VARIANT) RETURNS VARCHAR(MAX) AS
			BEGIN
				DECLARE @code AS VARCHAR(MAX);
				SELECT @code = code FROM custom_field_select_options WHERE ({{is_int:{$var1_ref}}}) AND id = CAST({$var1_ref} AS BIGINT);
				RETURN @code;
			END
		",
		"
		IF OBJECT_ID('FieldOptionWhereLabel', 'FN') IS NOT NULL DROP FUNCTION dbo.FieldOptionWhereLabel
		",
		"
		CREATE FUNCTION FieldOptionWhereLabel (@id SQL_VARIANT, @match VARCHAR(MAX)) RETURNS INT AS
			BEGIN
				DECLARE @exists AS VARCHAR(MAX);
				SELECT @exists = (
					CASE WHEN
						EXISTS(SELECT 1 FROM custom_field_select_options WHERE label = {$var2_ref} AND ({{is_int:{$var1_ref}}}) AND id = CAST({$var1_ref} AS BIGINT))
					THEN 1
					ELSE 0
					END
				);
				RETURN @exists;
			END
		",
		"
		IF OBJECT_ID('FieldOptionWhereCode', 'FN') IS NOT NULL DROP FUNCTION dbo.FieldOptionWhereCode
		",
		"
		CREATE FUNCTION FieldOptionWhereCode (@id SQL_VARIANT, @match VARCHAR(MAX)) RETURNS INT AS
			BEGIN
				DECLARE @exists AS VARCHAR(MAX);
				SELECT @exists = (
					CASE WHEN
						EXISTS(SELECT 1 FROM custom_field_select_options WHERE code = {$var2_ref} AND ({{is_int:{$var1_ref}}}) AND id = CAST({$var1_ref} AS BIGINT))
					THEN 1
					ELSE 0
					END
				);
				RETURN @exists;
			END
		"
	];
}

foreach($functions_sql as $sql) {
	Database::query(Database::preprocess($sql));
}

// Add the alias for 'gender' and set the system flag
$gender_sql = "
	UPDATE
		custom_fields
	SET
		alias = 'gender',
		system = 1
	WHERE
		source_class = 'SISStudent' AND
		legacy_id = 200000000
";

Database::query($gender_sql);

// Refresh the students views
SISStudent::refreshViews();

// End 8.0.2

// Begin 8.0.4

if(Database::columnExists('linked_fields', 'linked_value')) {
	Database::query("
		ALTER TABLE linked_fields DROP COLUMN linked_value
	");
}

foreach(['total', 'primary_sort', 'secondary_sort'] as $column) {
	if(!Database::columnExists('linked_fields', $column)) {
		Database::createColumn('linked_fields', $column, 'bigint');
	}
}

if(!Database::columnExists('linked_fields', 'computed_query')) {
	Database::createColumn('linked_fields', 'computed_query', 'text');
}

if(!Database::columnExists('linked_fields', 'field2')) {
	Database::createColumn('linked_fields', 'field2', 'varchar');
}

if(Database::columnExists('linked_fields', 'linked_field')) {
	Database::changeColumnType('linked_fields', 'linked_field', 'varchar');
	Database::renameColumn('linked_field', 'field1', 'linked_fields');
}

// Create the 'custom_field_categories_join_profiles' table
if(!Database::tableExists('custom_field_categories_join_profiles')) {
	Database::query("
		CREATE TABLE custom_field_categories_join_profiles (
			id BIGINT PRIMARY KEY,
			category_id BIGINT NOT NULL,
			profile_id NUMERIC NOT NULL, -- NUMERIC because foreign keys
			deleted BIGINT NULL
		)
	");
}

if(!Database::indexExists('custom_field_categories_join_profiles', 'cfcjp_category_id')) {
	Database::query("
		CREATE INDEX
			cfcjp_category_id
		ON
			custom_field_categories_join_profiles (category_id)
	");
}

if(!Database::indexExists('custom_field_categories_join_profiles', 'cfcjp_profile_id')) {
	Database::query("
		CREATE INDEX
			cfcjp_profile_id
		ON
			custom_field_categories_join_profiles (profile_id)
	");
}

$cfcjp_constraints = Database::getConstraints('custom_field_categories_join_profiles');

if(empty($cfcjp_constraints['cfcjp_category_id_fkey'])) {
	Database::query("
		ALTER TABLE
			custom_field_categories_join_profiles
		ADD CONSTRAINT
			cfcjp_category_id_fkey
		FOREIGN KEY
			(category_id)
		REFERENCES
			custom_field_categories(id)
	");
}

if(empty($cfcjp_constraints['cfcjp_profile_id_fkey'])) {
	Database::query("
		ALTER TABLE
			custom_field_categories_join_profiles
		ADD CONSTRAINT
			cfcjp_profile_id_fkey
		FOREIGN KEY
			(profile_id)
		REFERENCES
			user_profiles(id)
	");
}

if(!Database::sequenceExists('custom_field_categories_join_profiles_seq')) {
	Database::createSequence('custom_field_categories_join_profiles_seq');
}

// End 8.0.4
