<?php

Database::query("update FOCUS_FILES set FILE_EXPIRATION = '0' where SOURCE like 'CustomFields_%'");