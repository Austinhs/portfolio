<?php

if(!Database::columnExists('schedule', 'reauthorization_hours')) {
	Database::createColumn('schedule', 'reauthorization_hours', 'numeric');
}
