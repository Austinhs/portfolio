<?php

Database::query('UPDATE SCHEDULE_REQUESTS SET priority = NULL WHERE priority = 0');
