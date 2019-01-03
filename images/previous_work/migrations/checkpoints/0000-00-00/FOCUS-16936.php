<?php

if(!Database::columnExists('discipline_referrals','classroom'))
	Database::createColumn('discipline_referrals','classroom','varchar', '1');

if(!Database::indexExists('discipline_referrals','discipline_referrals_classroom_ind'))
	Database::query('create index discipline_referrals_classroom_ind on discipline_referrals (classroom) where classroom is not null');
