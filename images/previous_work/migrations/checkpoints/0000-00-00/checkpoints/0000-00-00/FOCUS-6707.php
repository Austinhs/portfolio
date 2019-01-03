<?php

if (!Database::columnExists('ps_programs', 'resident_tuition')) {
    Database::createColumn('ps_programs', 'resident_tuition', 'numeric');
}

if (!Database::columnExists('ps_programs', 'non_resident_tuition')) {
    Database::createColumn('ps_programs', 'non_resident_tuition', 'numeric');
}

if (!Database::columnExists('ps_programs', 'supplies')) {
    Database::createColumn('ps_programs', 'supplies', 'numeric');
}

if (!Database::columnExists('ps_programs', 'program_weeks')) {
    Database::createColumn('ps_programs', 'program_weeks', 'numeric');
}

echo 'Added tuition, supplies, and program weeks columns to Modify Programs';
