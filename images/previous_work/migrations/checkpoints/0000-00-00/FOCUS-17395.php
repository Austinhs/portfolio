<?php

if (!Database::columnExists("address_to_district", "zone_group")) {
	Database::createColumn("address_to_district", "zone_group", "VARCHAR", 150, true);
}

if (!Database::columnExists("school_choice_programs", "zone_group")) {
	Database::createColumn("school_choice_programs", "zone_group", "VARCHAR", 150, true);
}
