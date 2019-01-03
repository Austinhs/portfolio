<?php

Database::changeColumnType('people', 'first_name', 'VARCHAR', '255', true);
Database::changeColumnType('people', 'middle_name', 'VARCHAR', '255', true);
Database::changeColumnType('people', 'last_name', 'VARCHAR', '255', true);
