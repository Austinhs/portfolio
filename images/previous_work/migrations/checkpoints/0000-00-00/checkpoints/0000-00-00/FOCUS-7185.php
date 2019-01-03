<?php

if (!Database::columnExists('report_card_grade_scales', 'letter_only')) {
    Database::createColumn('report_card_grade_scales', 'letter_only', 'varchar(1)');
}
