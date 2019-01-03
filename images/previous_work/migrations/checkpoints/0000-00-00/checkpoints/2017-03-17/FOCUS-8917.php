<?php

if(!Database::columnExists('school_choice_tours_auditions', 'program')){
	Database::createColumn('school_choice_tours_auditions', 'program', 'integer');
}