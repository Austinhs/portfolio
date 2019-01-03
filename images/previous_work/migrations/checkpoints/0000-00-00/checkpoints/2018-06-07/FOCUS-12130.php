<?php

Database::query("UPDATE custom_field_log_columns SET deleted=1 WHERE type='computed_table'");