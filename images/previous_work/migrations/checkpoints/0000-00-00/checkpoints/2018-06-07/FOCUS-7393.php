<?php

if (!Database::columnExists('scheduling_teams', 'rollover_id')) {
    Database::createColumn('scheduling_teams', 'rollover_id', 'numeric');
}