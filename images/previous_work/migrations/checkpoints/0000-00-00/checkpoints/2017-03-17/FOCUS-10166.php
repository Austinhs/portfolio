<?php

$root     = realpath($GLOBALS['staticpath']);
$old_path = "{$root}/assets/themes/Default/logo.png";
$new_path = "{$root}/logo.png";

if(file_exists($new_path)) {
	rename($new_path, "{$new_path}.bak");
}

if(file_exists($old_path)) {
	rename($old_path, $new_path);
}
