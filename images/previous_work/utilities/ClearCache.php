<?php
	require_once __DIR__ . '/../Warehouse.php';

	$focusUser = preg_match('/@focusschoolsoftware.com$/', User('USERNAME'));

	if(!$focusUser && User('USERNAME') != 'focus') {
		die('Permission denied');
	}

	ini_set('display_errors', true);
	error_reporting(E_ALL);

	FocusCache::flush();
?>

<div>Cache cleared</div>

<pre>
<?= FocusCache::getEngine() ?>

<?= print_r(FocusCache::getServers(), true) ?>
</pre>
