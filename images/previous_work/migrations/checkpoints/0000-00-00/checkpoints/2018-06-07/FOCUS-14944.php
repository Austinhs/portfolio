<?php

if(!Database::columnExists("people_join_contacts", "detail_priority")) {
	Database::createColumn("people_join_contacts", "detail_priority", "bigint");
}
