<?php

if (Database::columnExists("grad_subjects", "request_group")) {
	Database::query("UPDATE grad_subjects SET request_group = '5' WHERE short_name  = 'FL'");
}
