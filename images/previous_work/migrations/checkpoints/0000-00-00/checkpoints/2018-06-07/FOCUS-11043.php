<?php

Database::query("UPDATE program_user_config SET value='NONE' WHERE title = 'ADDRESS' AND program = 'StudentFieldsView' AND value IS NULL");