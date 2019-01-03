<?php
Database::begin();
Database::query("DELETE FROM STUDENTS_JOIN_ADDRESS WHERE COALESCE(DELETED,0) != 0");
Database::commit();