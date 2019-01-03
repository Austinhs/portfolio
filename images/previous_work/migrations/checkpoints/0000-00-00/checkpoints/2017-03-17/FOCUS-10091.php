<?php

Database::query("DELETE FROM database_object_log WHERE record_class IN ('LoginHistory', 'RecentAccess', 'LoginToken')");
