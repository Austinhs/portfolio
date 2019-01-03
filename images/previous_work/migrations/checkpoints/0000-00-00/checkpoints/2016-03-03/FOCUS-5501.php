<?php

Migrations::depend('FOCUS-6359');
Migrations::depend('FOCUS-5468');

if(!Database::columnExists('custom_fields', 'visible_on_discipline_referral')) {
	Database::createColumn('custom_fields', 'visible_on_discipline_referral', 'bigint');
}
