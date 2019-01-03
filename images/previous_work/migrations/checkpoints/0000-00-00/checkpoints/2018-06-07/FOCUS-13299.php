<?php
if(!Database::columnExists('gradebook_assignment_tests', 'test_review_delay'))
{
	Database::createColumn('gradebook_assignment_tests', 'test_review_delay', 'numeric');
}
?>