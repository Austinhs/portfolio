<?php

Database::query("update importer_keys set required_fields = '[\"syear\"]' where table_name = 'course_periods';");