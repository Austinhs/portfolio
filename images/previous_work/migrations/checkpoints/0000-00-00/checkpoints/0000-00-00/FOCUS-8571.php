<?php

Database::query("delete from permission where \"key\" like '%SIS:CalendarEditEvents%' and exists (select '' from user_profiles p where p.id=permission.profile_id and p.profile!='admin')");
