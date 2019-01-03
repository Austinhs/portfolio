<?php

if (!Database::columnExists("community_app_link", "is_public")) {
	Database::createColumn("community_app_link", "is_public", "BIGINT");
}
