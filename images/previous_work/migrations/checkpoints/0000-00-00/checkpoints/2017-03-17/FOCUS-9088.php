<?php

if (Database::tableExists('letter_triggers')){
	Database::query("delete from letter_triggers where trigger_event in (7, 8)");
}