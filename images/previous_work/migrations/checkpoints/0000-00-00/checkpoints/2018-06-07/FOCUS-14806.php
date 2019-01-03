<?php

if(!Database::columnExists('edit_rules', 'logical_or')) {
	Database::createColumn('edit_rules', 'logical_or', 'bigint');
}
