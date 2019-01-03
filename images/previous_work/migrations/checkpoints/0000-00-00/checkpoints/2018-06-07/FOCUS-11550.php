<?php
if (Database::$type === 'postgres')
	Database::query("ALTER TABLE ps_programs ALTER COLUMN rollover_id SET DEFAULT NULL");
else
	Database::query("ALTER TABLE ps_programs ALTER COLUMN rollover_id INT NULL");
