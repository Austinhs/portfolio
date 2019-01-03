<?php

require '../Warehouse.php';

$profiles = array(
	'admin',
	'teacher',
	'parent',
	'student',
	'vendor'
);

if (empty($_GET['profile']) || !in_array($_GET['profile'], $profiles)) {
	$_GET['profile'] = $profiles[0];
}

$menu = new Menu($_GET['profile']);
?>

<form method="get" action="menuDebug.php">
	<select name="profile">
		<?php foreach ($profiles as $profile): ?>
			<option <?= $_GET['profile'] == $profile ? 'selected' : '' ?>><?=$profile?></option>
		<?php endforeach; ?>
	</select>
	<input type="submit">
</form>

<pre>
	<?php if (!empty($_GET['category'])): ?>
		<?=print_r($menu->getMenu(true)[$_GET['category']])?>
	<?php else: ?>
		<?=print_r($menu->getMenu(true))?>
	<?php endif; ?>
</pre>