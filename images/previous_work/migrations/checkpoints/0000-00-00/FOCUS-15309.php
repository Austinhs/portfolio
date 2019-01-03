<?php
Database::begin();
if (Database::columnExists('grad_subject_programs', 'syear')) {
	Database::dropColumn('grad_subject_programs', 'syear');
}
Database::commit();