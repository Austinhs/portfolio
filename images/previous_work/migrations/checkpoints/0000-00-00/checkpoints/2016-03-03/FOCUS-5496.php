<?php

Migrations::depend('FOCUS-6359');

if(!Database::columnExists('referral_actions', 'profiles_view')) {
	Database::createColumn('referral_actions', 'profiles_view', 'text');
}

if(!Database::columnExists('referral_actions', 'profiles_edit')) {
	Database::createColumn('referral_actions', 'profiles_edit', 'text');
}

if(!Database::columnExists('referral_codes', 'incident_type')) {
	Database::createColumn('referral_codes', 'incident_type', 'varchar');
}

if(Database::columnExists('referral_actions', 'warn_on_enroll')) {
	Database::query("ALTER TABLE referral_actions DROP COLUMN warn_on_enroll");
}

if(Database::columnExists('referral_actions', 'entry_message')) {
	Database::query("ALTER TABLE referral_actions DROP COLUMN entry_message");
}

if(Database::columnExists('referral_codes', 'warn_on_enroll')) {
	Database::query("ALTER TABLE referral_codes DROP COLUMN warn_on_enroll");
}

if(Database::columnExists('referral_codes', 'entry_message')) {
	Database::query("ALTER TABLE referral_codes DROP COLUMN entry_message");
}
