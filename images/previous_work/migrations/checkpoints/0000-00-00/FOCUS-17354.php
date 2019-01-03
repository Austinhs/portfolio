<?php
	if(Database::columnExists('fas_test_data', 'benchmark'))
	{
		Database::changeColumnType('fas_test_data', 'benchmark', 'text');
	}
?>