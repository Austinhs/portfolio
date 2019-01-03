<?php

if (!Database::tableExists('sss_accommodations')) {
	return false;
}

// According to Sara, all schools do not care if data is deleted
if (Database::columnExists('sss_accommodations', 'description')) {
	Database::dropColumn('sss_accommodations', 'description');
}
