<?php

Database::begin();

// User profile permissions
Database::query("
	WITH can_view_profiles AS (
		SELECT 
			p.profile_id 
		FROM 
			permission p 
		WHERE 
			p.\"key\" = 'Grades/GradReqsReport.php:can_view' 
			AND NOT EXISTS(
				SELECT 
					p2.profile_id 
				FROM 
					permission p2 
				WHERE 
					p2.profile_id = p.profile_id 
					AND p2.\"key\" = 'GraduationRequirements/GraduationRequirements.php:can_view'
			)
	) INSERT INTO permission (profile_id, \"key\") 
	SELECT 
		profile_id, 
		'GraduationRequirements/GraduationRequirements.php:can_view' AS \"key\" 
	FROM 
		can_view_profiles
");
Database::query("
	WITH can_edit_profiles AS (
		SELECT 
			p.profile_id 
		FROM 
			permission p 
		WHERE 
			p.\"key\" = 'Grades/GradReqsReport.php:can_edit' 
			AND NOT EXISTS(
				SELECT 
					p2.profile_id 
				FROM 
					permission p2 
				WHERE 
					p2.profile_id = p.profile_id 
					AND p2.\"key\" = 'GraduationRequirements/GraduationRequirements.php:can_edit'
			)
	) INSERT INTO permission (profile_id, \"key\") 
	SELECT 
		profile_id, 
		'GraduationRequirements/GraduationRequirements.php:can_edit' AS \"key\" 
	FROM 
		can_edit_profiles
");
Database::query("
	DELETE FROM
		permission
	WHERE
		\"key\" IN ('Grades/GradReqsReport.php:can_view', 'Grades/GradReqsReport.php:can_edit')
");

// User permissions
Database::query("
	WITH can_view_users AS (
		SELECT 
			p.user_id,
			p.comment
		FROM 
			user_permission p 
		WHERE 
			p.\"key\" = 'Grades/GradReqsReport.php:can_view' 
			AND NOT EXISTS(
				SELECT 
					p2.user_id 
				FROM 
					user_permission p2 
				WHERE 
					p2.user_id = p.user_id 
					AND p2.\"key\" = 'GraduationRequirements/GraduationRequirements.php:can_view'
			)
	) INSERT INTO user_permission (user_id, comment, \"key\") 
	SELECT 
		user_id,
		comment,
		'GraduationRequirements/GraduationRequirements.php:can_view' AS \"key\" 
	FROM 
		can_view_users
");
Database::query("
	WITH can_edit_users AS (
		SELECT 
			p.user_id,
			p.comment
		FROM 
			user_permission p 
		WHERE 
			p.\"key\" = 'Grades/GradReqsReport.php:can_edit' 
			AND NOT EXISTS(
				SELECT 
					p2.user_id 
				FROM 
					user_permission p2 
				WHERE 
					p2.user_id = p.user_id 
					AND p2.\"key\" = 'GraduationRequirements/GraduationRequirements.php:can_edit'
			)
	) INSERT INTO user_permission (user_id, comment, \"key\") 
	SELECT 
		user_id,
		comment,
		'GraduationRequirements/GraduationRequirements.php:can_edit' AS \"key\" 
	FROM 
		can_edit_users
");
Database::query("
	DELETE FROM
		user_permission
	WHERE
		\"key\" IN ('Grades/GradReqsReport.php:can_view', 'Grades/GradReqsReport.php:can_edit')
");

Database::commit();
