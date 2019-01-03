<?php

Migrations::depend('FOCUS-11459');
Migrations::depend('FOCUS-13336');

if(Database::columnExists('students','name_suffix')) {
    SISStudent::dropViews();
    Database::changeColumnType('students', 'name_suffix', 'varchar', 5);
    SISStudent::refreshViews();
}