<?php

Database::query("UPDATE course_periods set display_room=null where display_room=' '");
Database::query("UPDATE course_periods set display_room=null where display_room='  '");

?>