<?php

if(!Database::columnExists('discipline_referrals', 'closed')) {
	Database::createColumn('discipline_referrals', 'closed', 'VARCHAR', 1);
}


if(!Database::columnExists('discipline_referrals', 'returned')) {
	Database::createColumn('discipline_referrals', 'returned', 'VARCHAR', 1);
}

if(!Database::columnExists('discipline_referrals', 'comment')) {
	Database::createColumn('discipline_referrals', 'comment', 'TEXT');
}