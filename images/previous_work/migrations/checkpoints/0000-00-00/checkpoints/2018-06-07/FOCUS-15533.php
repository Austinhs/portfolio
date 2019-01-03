<?php
Database::begin();
Database::query("UPDATE EDIT_RULES SET CATEGORY ='FocusUser' WHERE CATEGORY = 'SISUser'");
Database::commit();