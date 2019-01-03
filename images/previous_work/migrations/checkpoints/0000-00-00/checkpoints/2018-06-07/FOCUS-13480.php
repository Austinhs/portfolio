<?php
Database::query("UPDATE user_enrollment set profiles=concat(',',profiles,',') where profiles not like '%,%'");