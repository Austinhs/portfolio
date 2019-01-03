<?php

Migrations::depend('FOCUS-6001');

// Fix sequence start values
if(Database::$type === 'mssql') {
	Database::query('ALTER SEQUENCE login_token_seq RESTART WITH 1');
	Database::query('ALTER SEQUENCE community_app_device_seq RESTART WITH 1');
	Database::query('ALTER SEQUENCE community_app_link_seq RESTART WITH 1');
	Database::query('ALTER SEQUENCE community_app_link_profile_seq RESTART WITH 1');
	Database::query('ALTER SEQUENCE community_app_feed_seq RESTART WITH 1');
	Database::query('ALTER SEQUENCE community_app_feed_subscription_seq RESTART WITH 1');
	Database::query('ALTER SEQUENCE community_app_school_subscription_seq RESTART WITH 1');
	Database::query('ALTER SEQUENCE community_app_notification_seq RESTART WITH 1');
	Database::query('ALTER SEQUENCE community_app_notification_device_seq RESTART WITH 1');
}
