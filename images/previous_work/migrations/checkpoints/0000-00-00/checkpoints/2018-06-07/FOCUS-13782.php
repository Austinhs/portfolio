<?php

if (!Database::columnExists('grad_subjects', 'min_syear')) {
	Database::query("ALTER TABLE grad_subjects ADD min_syear int");
}

if (!Database::columnExists('grad_subjects', 'max_syear')) {
	Database::query("ALTER TABLE grad_subjects ADD max_syear int");
}
