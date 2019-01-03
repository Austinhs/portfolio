<?php

// Tags: Formbuilder
if(Database::tableExists('gl_requests')){
	Database::query("UPDATE gl_requests SET status='P' WHERE status='Pending Review'");
	Database::query("UPDATE gl_requests SET status='Y' WHERE status='Approved'");
	Database::query("UPDATE gl_requests SET status='D' WHERE status='Denied'");
	Database::query("UPDATE gl_requests SET status=null WHERE status='Draft'");
}
