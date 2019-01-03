<?php

if(!Database::columnExists('header_templates', 'available_for')) {
	Database::createColumn('header_templates', 'available_for', 'text');
}
