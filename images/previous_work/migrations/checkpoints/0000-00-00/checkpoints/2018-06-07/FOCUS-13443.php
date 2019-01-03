<?php
Database::query("UPDATE program_user_config SET value = 'alpha' WHERE program = 'BenchmarkCards' AND (value = ' ' OR value IS NULL) AND title = 'sort_course'");