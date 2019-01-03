<?php
if(!Database::columnExists('school_choice_priority_rankings', 'cap')){
	Database::createColumn('school_choice_priority_rankings', 'cap', 'bigint');
}