<?php

if(!Database::columnExists('portal_notes', 'public_note')) {
	Database::createColumn('portal_notes', 'public_note', 'BIGINT');
}
