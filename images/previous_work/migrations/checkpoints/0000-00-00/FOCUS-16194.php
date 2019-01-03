<?php

if (!Database::sequenceExists("community_app_news_settings_seq")) {
	Database::createSequence("community_app_news_settings_seq");
}

if (!Database::tableExists("community_app_news_settings")) {
	Database::query("
		CREATE TABLE community_app_news_settings (
			id BIGINT PRIMARY KEY,
			school_id BIGINT NOT NULL,
			disable_news BIGINT
		)
	");
}
