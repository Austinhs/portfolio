<?php
Database::query("delete from program_config where syear is null and school_id is null and title!='DEFAULT_S_YEAR' and program='system'");