<?php

if(!Database::columnExists('focus_files', 'box_id')) {
	Database::createColumn('focus_files', 'box_id', 'bigint');
}
