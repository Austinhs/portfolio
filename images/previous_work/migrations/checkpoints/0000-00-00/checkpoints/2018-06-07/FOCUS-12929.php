<?php

if(!Database::columnExists('letter_queue_text', 'pending')) {
	Database::createColumn('letter_queue_text', 'pending', 'NUMERIC', 1);
}
