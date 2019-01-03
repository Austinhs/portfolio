<?php
Database::query("UPDATE schedule_requests SET marking_period_id = NULL WHERE marking_period_id = 0");
