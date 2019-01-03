<?php

if(!Database::columnExists('gradebook_assignments', 'hide_from_excluded')) {
    Database::createColumn('gradebook_assignments', 'hide_from_excluded', 'varchar', 1);
}