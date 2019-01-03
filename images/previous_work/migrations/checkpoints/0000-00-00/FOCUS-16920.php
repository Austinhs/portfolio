<?php

Database::begin();


// Create standards sequences if they don't exist
$standards_next = Database::get("SELECT max(id)+1 AS next FROM standards")[0]["NEXT"];
$sc1_next       = Database::get("SELECT max(id)+1 AS next FROM standard_categories_1")[0]["NEXT"];
$sc2_next       = Database::get("SELECT max(id)+1 AS next FROM standard_categories_2")[0]["NEXT"];
$sc3_next       = Database::get("SELECT max(id)+1 AS next FROM standard_categories_3")[0]["NEXT"];
$sc4_next       = Database::get("SELECT max(id)+1 AS next FROM standard_categories_4")[0]["NEXT"];

if(!Database::sequenceExists("standards_seq")) {
	Database::createSequence("standards_seq", $standards_next);
}
if(!Database::sequenceExists("standard_categories_1_seq")) {
	Database::createSequence("standard_categories_1_seq", $sc1_next);
}
if(!Database::sequenceExists("standard_categories_2_seq")) {
	Database::createSequence("standard_categories_2_seq", $sc2_next);
}
if(!Database::sequenceExists("standard_categories_3_seq")) {
	Database::createSequence("standard_categories_3_seq", $sc3_next);
}
if(!Database::sequenceExists("standard_categories_4_seq")) {
	Database::createSequence("standard_categories_4_seq", $sc4_next);
}


// Make sure the sequences begin after the largest id in each table
$standards_seq_current = Database::nextValue("standards_seq");
$sc1_seq_current       = Database::nextValue("standard_categories_1_seq");
$sc2_seq_current       = Database::nextValue("standard_categories_2_seq");
$sc3_seq_current       = Database::nextValue("standard_categories_3_seq");
$sc4_seq_current       = Database::nextValue("standard_categories_4_seq");

if($standards_seq_current < $standards_next) {
	Database::query("ALTER SEQUENCE standards_seq RESTART WITH {$standards_next}");
}
if($sc1_seq_current < $sc1_next) {
	Database::query("ALTER SEQUENCE standard_categories_1_seq RESTART WITH {$sc1_next}");
}
if($sc2_seq_current < $sc2_next) {
	Database::query("ALTER SEQUENCE standard_categories_2_seq RESTART WITH {$sc2_next}");
}
if($sc3_seq_current < $sc3_next) {
	Database::query("ALTER SEQUENCE standard_categories_3_seq RESTART WITH {$sc3_next}");
}
if($sc4_seq_current < $sc4_next) {
	Database::query("ALTER SEQUENCE standard_categories_4_seq RESTART WITH {$sc4_next}");
}


// FOCUS-13584 removed the identity column from standard_categories_1 and standard_categories_2
if(Database::$type === "mssql")
{
	Database::removeIdentityColumn("standard_categories_3");
	Database::removeIdentityColumn("standard_categories_4");
}

Database::commit();
