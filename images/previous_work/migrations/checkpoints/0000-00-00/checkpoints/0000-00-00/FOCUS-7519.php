<?php
Migrations::depend('FOCUS-7661');
Database::changeColumnType('scheduler_sections', 'short_name', 'varchar', 25);