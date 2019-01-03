<?php

if(!Database::columnExists('gradebook_templates_assignments', 'benchmarks')) {
	Database::createColumn('gradebook_templates_assignments', 'benchmarks', 'text');
}