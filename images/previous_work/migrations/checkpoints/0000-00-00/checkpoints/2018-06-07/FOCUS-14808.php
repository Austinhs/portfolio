<?php

// delete log column permissions
$delete_cflg_permissions_sql = "
WITH delete_keys AS (
SELECT CONCAT(cf.source_class, ':', cf.id, '#', cflc.id, ':can_%') AS pattern FROM custom_field_log_columns cflc JOIN custom_fields cf ON cf.id = cflc.field_id WHERE COALESCE(cf.deleted, cflc.deleted, 0) != 0
),
delete_permissions AS (
SELECT permission.id FROM permission, delete_keys d WHERE permission.\"key\" LIKE d.pattern
) DELETE FROM permission WHERE id IN (SELECT id FROM delete_permissions)";


// delete custom fields permissions
$delete_cf_permissions_sql = "
WITH delete_keys AS (
SELECT CONCAT(cf.source_class, ':', cf.id, ':can_%') AS pattern FROM custom_fields cf WHERE COALESCE(cf.deleted, 0) != 0
),
delete_permissions AS (
SELECT permission.id FROM permission, delete_keys d WHERE permission.\"key\" LIKE d.pattern
) DELETE FROM permission WHERE id IN (SELECT id FROM delete_permissions)";

Database::query($delete_cflg_permissions_sql);
Database::query($delete_cf_permissions_sql);