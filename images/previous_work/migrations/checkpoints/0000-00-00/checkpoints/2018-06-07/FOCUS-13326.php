<?php

if(!Database::columnExists('user_enrollment', 'erp_profiles')) {
	Database::createColumn('user_enrollment', 'erp_profiles', 'text');
}
