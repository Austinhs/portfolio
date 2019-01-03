<?php 
foreach(glob("templates/*") as $template) {
	require_once($template);
	var_dump($template);
}

echo "test";
