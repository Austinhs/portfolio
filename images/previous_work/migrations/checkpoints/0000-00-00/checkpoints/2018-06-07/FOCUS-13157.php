<?php

Database::query("DELETE FROM PROGRAM_CONFIG WHERE TITLE = 'EMAIL_NOTIFICATIONS' AND PROGRAM != 'system'");
