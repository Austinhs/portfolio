<?php

if(!Database::columnExists('people', 'notes')) {
	Database::createColumn('people', 'notes', 'text');
}
