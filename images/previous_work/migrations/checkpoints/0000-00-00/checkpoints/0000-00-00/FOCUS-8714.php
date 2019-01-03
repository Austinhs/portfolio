<?php
if(!Database::sequenceExists('school_choice_application_status_seq')){
  Database::createSequence('school_choice_application_status_seq');
}

Database::query("delete from school_choice_priorities where abbr = 'SC'");

if(empty(Database::get("select * from school_choice_priorities where abbr = 'SCA'"))){
  Database::query("insert into school_choice_priorities (abbr, title) values ('SCA', 'Special Circumstances A')");
}
if(empty(Database::get("select * from school_choice_priorities where abbr = 'SCB'"))){
  Database::query("insert into school_choice_priorities (abbr, title) values ('SCB', 'Special Circumstances B')");
}

Database::changeColumnType('school_choice_priority_charts', 'programs', 'VARCHAR', '5000');
Database::changeColumnType('school_choice_application_notes', 'programs', 'VARCHAR', '5000');
