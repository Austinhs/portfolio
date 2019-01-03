<?php

if(!Database::indexExists('grad_subject_programs','grad_subject_programs_pkey'))
	Database::query("alter table grad_subject_programs add constraint grad_subject_programs_pkey primary key (id)");