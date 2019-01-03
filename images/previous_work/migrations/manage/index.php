<?php

$error_reporting = error_reporting(E_ALL);
$display_errors  = ini_set('display_errors', 1);

$GLOBALS['disable_login_redirect'] = true;

require_once('../../Warehouse.php');

// Login if necessary
if(isset($_POST['username']) && isset($_POST['password'])) {
	$username = $_POST['username'];
	$password = $_POST['password'];

	// Don't log the attempt (removes dependency on DatabaseObject)
	Authenticate::attemptLogin($username, $password, [], [], [], false, false);
	header("Location: {$_SERVER['PHP_SELF']}");
	exit;
}

$paths = [
	FocusModule::TEMPLATES,
	FocusModule::SASS,
	__DIR__ . '/assets'
];

if(isset($_REQUEST['__call__'])) {
	FocusModule::render($paths);
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Manage Migrations</title>
	</head>
	<body>
		<main></main>
		<div class="hidden modal">
			<div class="modal-inner"></div>
		</div>
	</body>
</html>

<?php

$allowed = false;

try {
	// Test the current user's access
	Migrations::checkUsername();

	// Set the allowed flag
	$allowed = true;
}
catch(Exception $e) {}

FocusModule::render($paths, [ 'allowed' => $allowed ]);

ini_set('display_errors', $display_errors);
error_reporting($error_reporting);
