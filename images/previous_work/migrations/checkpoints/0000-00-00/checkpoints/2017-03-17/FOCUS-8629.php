<?php

Database::query("DELETE FROM program_config WHERE title = 'ENABLE_USER_PERMISSION' AND program != 'system'");
