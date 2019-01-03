<?php

Migrations::depend('FOCUS-6359');

Database::query("UPDATE permission SET \"key\" = 'package::Finance' WHERE \"key\" = 'package::Finance::ERP'
and not exists (select '' from permission p2 where p2.\"key\"='package::Finance' and p2.profile_id=permission.profile_id)
");

Database::query("DELETE FROM permission WHERE \"key\" = 'package::Finance::ERP'");

