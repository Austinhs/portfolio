<?php
Database::query(
    "UPDATE custom_fields
    SET max_length = NULL
    WHERE (type = 'text' OR type = 'textarea')
    AND max_length=0;"
);
