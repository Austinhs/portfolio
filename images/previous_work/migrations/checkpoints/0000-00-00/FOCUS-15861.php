<?php

	// This is the same migration as FOCUS-13962
	if(!Database::columnExists('schools', 'min_syear')) {
		Database::createColumn('schools', 'min_syear', 'INT');
	}

	if(!Database::columnExists('schools', 'max_syear')) {
		Database::createColumn('schools', 'max_syear', 'INT');
	}
